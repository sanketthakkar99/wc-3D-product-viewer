/**
 * WC 3D Product Viewer – theme slider injector (Porto and other Owl Carousel / custom galleries).
 *
 * Printed inline right after the theme's product gallery markup, so it runs BEFORE the theme
 * initialises its slider. It copies one of the theme's own slides and thumbnails, swaps the image
 * for the 3D viewer, and appends one slide + one thumbnail per model. The theme then builds its
 * slider with the 3D slides already inside it. If the slider is already running, the Owl Carousel
 * API is used instead.
 */
( function ( script ) {
	'use strict';

	var box = script && script.previousElementSibling;
	if ( ! box || ! box.classList || ! box.classList.contains( 'wc3d-pending' ) ) {
		return;
	}

	var doc = document;
	var scope = box.parentNode;
	var $ = window.jQuery;
	var sources = Array.prototype.slice.call( box.children );

	function find( selector ) {
		if ( ! selector ) {
			return null;
		}
		try {
			// Prefer an element near our markup, then anywhere on the page.
			var near = scope;
			for ( var i = 0; i < 3 && near; i++ ) {
				var hit = near.querySelector( selector );
				if ( hit ) {
					return hit;
				}
				near = near.parentElement;
			}
			return doc.querySelector( selector );
		} catch ( e ) {
			return null;
		}
	}

	function owl( el ) {
		return $ && el && $( el ).data( 'owl.carousel' ) ? $( el ) : null;
	}

	/** Slides/thumbnails of a container (works before and after Owl has initialised). */
	function items( container ) {
		var stage = container.querySelector( '.owl-stage' );
		var list = stage ?
			Array.prototype.map.call( stage.querySelectorAll( '.owl-item:not(.cloned)' ), function ( it ) {
				return it.firstElementChild;
			} ) :
			Array.prototype.slice.call( container.children );
		return list.filter( function ( el ) {
			return el && el.nodeType === 1 && el.tagName !== 'SCRIPT' && el.tagName !== 'TEMPLATE' &&
				! el.classList.contains( 'wc3d-pending' ) && ! el.hasAttribute( 'data-wc3d-index' );
		} );
	}

	function stripIds( el ) {
		el.removeAttribute( 'id' );
		Array.prototype.forEach.call( el.querySelectorAll( '[id]' ), function ( n ) {
			n.removeAttribute( 'id' );
		} );
	}

	function copyData( from, to ) {
		[ 'data-thumb', 'data-thumb-alt', 'data-wc3d-badge', 'data-wc3d-index' ].forEach( function ( a ) {
			if ( from.hasAttribute( a ) ) {
				to.setAttribute( a, from.getAttribute( a ) );
			}
		} );
	}

	/** Theme slide markup with the image swapped for the 3D viewer. */
	function makeSlide( template, src ) {
		var wrap = src.querySelector( '.wc3d-wrap' );
		var el = null;

		if ( template ) {
			el = template.cloneNode( true );
			var img = el.tagName === 'IMG' ? el : el.querySelector( 'img' );
			var target = img;
			if ( img ) {
				var link = img.closest( 'a' );
				if ( link && ( link === el || el.contains( link ) ) ) {
					target = link; // drop lightbox links
				}
			}
			if ( ! target || target === el ) {
				var div = doc.createElement( 'div' );
				div.className = el.className;
				el = div;
				el.appendChild( wrap );
			} else {
				target.parentNode.replaceChild( wrap, target );
				// Remove any other images / zoom overlays copied from the photo slide.
				Array.prototype.forEach.call( el.querySelectorAll( 'img, picture, a, .zoomContainer, .zoom' ), function ( n ) {
					if ( ! wrap.contains( n ) ) {
						n.parentNode.removeChild( n );
					}
				} );
			}
			stripIds( el );
			[ 'active', 'selected', 'current', 'flex-active-slide', 'slick-current', 'swiper-slide-active' ].forEach( function ( c ) {
				el.classList.remove( c );
			} );
		} else {
			el = doc.createElement( 'div' );
			el.appendChild( wrap );
		}

		el.classList.add( 'wc3d-gallery-slide', 'wc3d-theme-slide' );
		copyData( src, el );
		return el;
	}

	/** Theme thumbnail markup pointing at the 3D thumbnail image. */
	function makeThumb( template, src ) {
		var url = src.getAttribute( 'data-thumb' );
		var alt = src.getAttribute( 'data-thumb-alt' ) || '';
		var el = template ? template.cloneNode( true ) : doc.createElement( 'div' );
		var img = el.tagName === 'IMG' ? el : el.querySelector( 'img' );

		if ( ! img ) {
			img = doc.createElement( 'img' );
			el.appendChild( img );
		}
		Array.prototype.forEach.call( el.querySelectorAll( 'img' ), function ( n ) {
			if ( n !== img ) {
				n.parentNode.removeChild( n );
			}
		} );

		img.src = url;
		img.alt = alt;
		[ 'srcset', 'sizes', 'data-srcset', 'data-lazy-srcset', 'width', 'height', 'loading' ].forEach( function ( a ) {
			img.removeAttribute( a );
		} );
		[ 'data-src', 'data-lazy-src', 'data-original', 'data-large_image' ].forEach( function ( a ) {
			if ( img.hasAttribute( a ) ) {
				img.setAttribute( a, url );
			}
		} );
		[ 'owl-lazy', 'lazyload', 'lazy' ].forEach( function ( c ) {
			img.classList.remove( c );
		} );

		stripIds( el );
		[ 'active', 'selected', 'current', 'flex-active', 'slick-current', 'swiper-slide-thumb-active' ].forEach( function ( c ) {
			el.classList.remove( c );
		} );

		el.classList.add( 'wc3d-thumb', 'wc3d-theme-thumb' );
		var badge = src.getAttribute( 'data-wc3d-badge' );
		if ( badge ) {
			el.classList.add( 'wc3d-thumb--badge' );
			el.setAttribute( 'data-wc3d-badge', badge );
		}
		el.setAttribute( 'data-wc3d-index', src.getAttribute( 'data-wc3d-index' ) );
		el.setAttribute( 'title', alt );
		return el;
	}

	var main = find( box.getAttribute( 'data-main' ) );

	if ( ! main || ! sources.length ) {
		// Slider not found: keep the models visible right below the gallery instead of losing them.
		box.hidden = false;
		box.classList.remove( 'wc3d-pending' );
		box.classList.add( 'wc3d-fallback' );
		return;
	}

	var thumbs = find( box.getAttribute( 'data-thumbs' ) );
	var slideTemplate = items( main ).pop() || null;
	var thumbTemplate = thumbs ? ( items( thumbs ).pop() || null ) : null;
	var $main = owl( main );
	var $thumbs = owl( thumbs );

	sources.forEach( function ( src ) {
		var slide = makeSlide( slideTemplate, src );
		if ( $main ) {
			$main.trigger( 'add.owl.carousel', [ $( slide ) ] );
		} else {
			main.appendChild( slide );
		}

		if ( thumbs ) {
			var thumb = makeThumb( thumbTemplate, src );
			if ( $thumbs ) {
				$thumbs.trigger( 'add.owl.carousel', [ $( thumb ) ] );
			} else {
				thumbs.appendChild( thumb );
			}
		}
	} );

	if ( $main ) {
		$main.trigger( 'refresh.owl.carousel' );
	}
	if ( $thumbs ) {
		$thumbs.trigger( 'refresh.owl.carousel' );
	}

	main.setAttribute( 'data-wc3d-main', '1' );
	if ( thumbs ) {
		thumbs.setAttribute( 'data-wc3d-thumbs', '1' );
	}
	( main.closest( '.product-images, .images, .woocommerce-product-gallery' ) || main.parentNode ).classList.add( 'wc3d-has-model' );
	box.parentNode.removeChild( box );
} )( document.currentScript );
