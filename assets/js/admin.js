/* WC 3D Product Viewer – product edit screen (multiple models). */
( function ( $ ) {
	'use strict';

	var cfg = window.wc3dAdmin || { extensions: [ 'glb', 'gltf' ], max: 20, i18n: {} };
	var modelFrame, thumbFrame, $thumbRow = null;

	function ext( name ) {
		var m = /\.([a-z0-9]+)$/i.exec( name || '' );
		return m ? m[ 1 ].toLowerCase() : '';
	}

	function formatSize( bytes ) {
		if ( ! bytes ) {
			return '';
		}
		var units = [ 'B', 'KB', 'MB', 'GB' ], i = 0;
		while ( bytes >= 1024 && i < units.length - 1 ) {
			bytes /= 1024;
			i++;
		}
		return bytes.toFixed( i ? 1 : 0 ) + ' ' + units[ i ];
	}

	$( function () {
		var $box = $( '.wc3d-box' );
		if ( ! $box.length ) {
			return;
		}
		var $list = $box.find( '.wc3d-models' );
		var tpl = document.getElementById( 'wc3d-row-template' );

		function refresh() {
			var empty = ! $list.children( '.wc3d-model' ).length;
			$list.toggleClass( 'is-empty', empty );
			$box.find( '.wc3d-empty' ).prop( 'hidden', ! empty );
		}

		function existingIds() {
			return $list.find( '.wc3d-model-id' ).map( function () {
				return String( this.value );
			} ).get();
		}

		function addRow( a ) {
			var $row = $( tpl.content.cloneNode( true ) ).children( '.wc3d-model' );
			var name = a.filename || ( a.url || '' ).split( '/' ).pop();
			$row.find( '.wc3d-model-id' ).val( a.id );
			$row.find( '.wc3d-model__name' ).text( name ).attr( 'title', name );
			$row.find( '.wc3d-model__size' ).text( a.filesizeHumanReadable || formatSize( a.filesizeInBytes ) );
			$list.append( $row );
		}

		// Drag to reorder (same order as gallery slides).
		$list.sortable( {
			items: '> .wc3d-model',
			handle: '.wc3d-model__handle',
			axis: 'y',
			placeholder: 'wc3d-model wc3d-model--placeholder',
			forcePlaceholderSize: true,
			tolerance: 'pointer'
		} );

		// ---- Add models (multi-select) ----
		$box.on( 'click', '.wc3d-add-models', function ( e ) {
			e.preventDefault();
			if ( ! modelFrame ) {
				modelFrame = wp.media( {
					title: cfg.i18n.modelTitle,
					button: { text: cfg.i18n.modelButton },
					library: { type: [ 'model/gltf-binary', 'model/gltf+json', 'application/octet-stream' ] },
					multiple: 'add'
				} );
				modelFrame.on( 'select', function () {
					var have = existingIds();
					var skipped = [];
					var limitHit = false;
					modelFrame.state().get( 'selection' ).each( function ( att ) {
						var a = att.toJSON();
						var name = a.filename || a.url;
						if ( cfg.extensions.indexOf( ext( name ) ) === -1 ) {
							skipped.push( name );
							return;
						}
						if ( have.indexOf( String( a.id ) ) !== -1 ) {
							return; // already added
						}
						if ( have.length >= cfg.max ) {
							limitHit = true;
							return;
						}
						addRow( a );
						have.push( String( a.id ) );
					} );
					modelFrame.state().get( 'selection' ).reset();
					refresh();
					if ( skipped.length ) {
						window.alert( cfg.i18n.wrongType.replace( '%s', skipped.join( ', ' ) ) );
					}
					if ( limitHit ) {
						window.alert( cfg.i18n.tooMany.replace( '%d', cfg.max ) );
					}
				} );
			}
			modelFrame.open();
		} );

		// ---- Remove ----
		$box.on( 'click', '.wc3d-model__remove', function ( e ) {
			e.preventDefault();
			$( this ).closest( '.wc3d-model' ).remove();
			refresh();
		} );

		// ---- Per-model thumbnail ----
		$box.on( 'click', '.wc3d-thumb-choose, .wc3d-model__thumb', function ( e ) {
			e.preventDefault();
			$thumbRow = $( this ).closest( '.wc3d-model' );
			if ( ! thumbFrame ) {
				thumbFrame = wp.media( {
					title: cfg.i18n.thumbTitle,
					button: { text: cfg.i18n.thumbButton },
					library: { type: 'image' },
					multiple: false
				} );
				thumbFrame.on( 'select', function () {
					if ( ! $thumbRow ) {
						return;
					}
					var a = thumbFrame.state().get( 'selection' ).first().toJSON();
					var url = a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url;
					$thumbRow.find( '.wc3d-model-thumb' ).val( a.id );
					$thumbRow.find( '.wc3d-model__thumb' ).addClass( 'has-image' ).find( 'img' ).attr( 'src', url );
					$thumbRow.find( '.wc3d-thumb-reset' ).prop( 'hidden', false );
				} );
			}
			thumbFrame.open();
		} );

		$box.on( 'click', '.wc3d-thumb-reset', function ( e ) {
			e.preventDefault();
			var $row = $( this ).closest( '.wc3d-model' );
			var $img = $row.find( '.wc3d-model__thumb img' );
			$row.find( '.wc3d-model-thumb' ).val( '' );
			$img.attr( 'src', $img.data( 'default' ) );
			$row.find( '.wc3d-model__thumb' ).removeClass( 'has-image' );
			$( this ).prop( 'hidden', true );
		} );

		refresh();
	} );
} )( jQuery );
