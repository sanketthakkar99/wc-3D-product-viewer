/**
 * WC 3D Product Viewer – front end.
 *
 * - Loads the <model-viewer> engine on demand (when the 3D slide is opened or scrolled into view).
 * - Keeps the gallery slider (WooCommerce FlexSlider, Porto/Owl Carousel, …) from swiping while the shopper rotates the model.
 * - Marks the 3D thumbnail, hides the image-zoom trigger on the 3D slide.
 * - Progress bar, reset view and full-screen buttons.
 */
( function () {
	'use strict';

	var settings = window.wc3dSettings || {};
	var engineRequested = false;

	/** Inject the model-viewer module script once. */
	function loadEngine() {
		if ( engineRequested || ( window.customElements && window.customElements.get( 'model-viewer' ) ) ) {
			engineRequested = true;
			return;
		}
		engineRequested = true;
		var s = document.createElement( 'script' );
		s.type = 'module';
		s.src = settings.viewerUrl;
		document.head.appendChild( s );
	}

	/** Stop sliders (FlexSlider, Swiper, Slick, Flickity…) from treating model drags as swipes. */
	function isolateGestures( wrap ) {
		var viewer = wrap.querySelector( 'model-viewer' );
		if ( ! viewer ) {
			return;
		}
		[ 'touchstart', 'touchmove', 'touchend', 'pointerdown', 'pointermove', 'mousedown', 'mousemove' ].forEach( function ( type ) {
			viewer.addEventListener( type, function ( e ) {
				e.stopPropagation();
			}, { passive: true } );
		} );
	}

	/** Progress bar, error message, hint, reset and full-screen. */
	function setupControls( wrap ) {
		var viewer = wrap.querySelector( 'model-viewer' );
		if ( ! viewer ) {
			return;
		}
		var bar = wrap.querySelector( '.wc3d-progress__bar' );
		var progress = wrap.querySelector( '.wc3d-progress' );

		viewer.addEventListener( 'progress', function ( e ) {
			var p = e.detail && typeof e.detail.totalProgress === 'number' ? e.detail.totalProgress : 0;
			if ( bar ) {
				bar.style.width = Math.round( p * 100 ) + '%';
			}
			if ( progress ) {
				progress.classList.toggle( 'is-done', p >= 1 );
			}
		} );

		viewer.addEventListener( 'load', function () {
			wrap.classList.add( 'is-loaded' );
		} );

		viewer.addEventListener( 'error', function () {
			wrap.classList.add( 'has-error' );
			var badge = wrap.querySelector( '.wc3d-poster__badge' );
			if ( badge && settings.i18n ) {
				badge.textContent = settings.i18n.error;
			}
		} );

		viewer.addEventListener( 'camera-change', function ( e ) {
			if ( e.detail && e.detail.source === 'user-interaction' ) {
				wrap.classList.add( 'has-interacted' );
			}
		} );

		var reset = wrap.querySelector( '.wc3d-reset' );
		if ( reset ) {
			reset.addEventListener( 'click', function () {
				viewer.cameraOrbit = viewer.getAttribute( 'camera-orbit' ) || 'auto auto auto';
				viewer.cameraTarget = viewer.getAttribute( 'camera-target' ) || 'auto auto auto';
				viewer.fieldOfView = viewer.getAttribute( 'field-of-view' ) || 'auto';
				if ( typeof viewer.resetTurntableRotation === 'function' ) {
					viewer.resetTurntableRotation();
				}
			} );
		}

		var fs = wrap.querySelector( '.wc3d-fullscreen' );
		if ( fs ) {
			fs.addEventListener( 'click', function () {
				toggleFullscreen( wrap );
			} );
			document.addEventListener( 'keydown', function ( e ) {
				if ( e.key === 'Escape' && wrap.classList.contains( 'is-pseudo-fullscreen' ) ) {
					toggleFullscreen( wrap );
				}
			} );
		}
	}

	function toggleFullscreen( wrap ) {
		var fsEl = document.fullscreenElement || document.webkitFullscreenElement;
		if ( fsEl ) {
			( document.exitFullscreen || document.webkitExitFullscreen ).call( document );
			return;
		}
		if ( wrap.classList.contains( 'is-pseudo-fullscreen' ) ) {
			wrap.classList.remove( 'is-pseudo-fullscreen' );
			document.documentElement.classList.remove( 'wc3d-no-scroll' );
			return;
		}
		var req = wrap.requestFullscreen || wrap.webkitRequestFullscreen;
		if ( req ) {
			try {
				var r = req.call( wrap );
				if ( r && typeof r.catch === 'function' ) {
					r.catch( function () {
						pseudoFullscreen( wrap );
					} );
				}
				return;
			} catch ( err ) {
				// Fall through to CSS full screen (iPhone Safari has no element full-screen API).
			}
		}
		pseudoFullscreen( wrap );
	}

	function pseudoFullscreen( wrap ) {
		wrap.classList.add( 'is-pseudo-fullscreen' );
		document.documentElement.classList.add( 'wc3d-no-scroll' );
	}

	/** Load the engine when an element scrolls into view. */
	function loadWhenVisible( el ) {
		if ( ! ( 'IntersectionObserver' in window ) ) {
			loadEngine();
			return;
		}
		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					loadEngine();
					io.disconnect();
				}
			} );
		}, { rootMargin: '200px' } );
		io.observe( el );
	}

	/** Gallery integration for one 3D slide. */
	function setupGallerySlide( slide ) {
		var gallery = slide.closest( '.woocommerce-product-gallery' ) || slide.parentNode;

		// 1. Active-slide tracking (FlexSlider adds .flex-active-slide).
		function syncActive() {
			// Several 3D slides can sit next to each other: the gallery is "3D active" if ANY of them is shown.
			gallery.classList.toggle( 'wc3d-active', !! gallery.querySelector( '.wc3d-gallery-slide.flex-active-slide' ) );
			if ( slide.classList.contains( 'flex-active-slide' ) ) {
				loadEngine();
			}
		}
		new MutationObserver( syncActive ).observe( slide, { attributes: true, attributeFilter: [ 'class' ] } );

		// 2. Thumbnail: FlexSlider builds <ol class="flex-control-thumbs"> after init.
		function markThumb() {
			var thumbs = gallery.querySelectorAll( '.flex-control-thumbs li' );
			if ( ! thumbs.length ) {
				return false;
			}
			var slides = Array.prototype.slice.call( gallery.querySelectorAll( '.woocommerce-product-gallery__image' ) );
			var li = thumbs[ slides.indexOf( slide ) ];
			if ( ! li || li.classList.contains( 'wc3d-thumb' ) ) {
				return true;
			}
			li.classList.add( 'wc3d-thumb' );
			var badge = slide.getAttribute( 'data-wc3d-badge' );
			if ( badge ) {
				li.classList.add( 'wc3d-thumb--badge' );
				li.setAttribute( 'data-wc3d-badge', badge );
			}
			var img = li.querySelector( 'img' );
			if ( img && ! img.getAttribute( 'alt' ) ) {
				img.setAttribute( 'alt', slide.getAttribute( 'data-thumb-alt' ) || '' );
			}
			// Warm up the engine as soon as the shopper reaches for the 3D thumbnail.
			[ 'pointerenter', 'touchstart', 'focusin' ].forEach( function ( type ) {
				li.addEventListener( type, loadEngine, { passive: true, once: true } );
			} );
			return true;
		}
		if ( ! markThumb() ) {
			var mo = new MutationObserver( function () {
				if ( markThumb() ) {
					mo.disconnect();
				}
			} );
			mo.observe( gallery, { childList: true, subtree: true } );
		}

		// 3. No slider (theme disabled it): slides are stacked, so load when visible.
		function checkSlider() {
			setTimeout( function () {
				if ( ! slide.closest( '.flex-viewport' ) ) {
					loadWhenVisible( slide );
				}
				syncActive();
			}, 300 );
		}
		if ( document.readyState === 'complete' ) {
			checkSlider();
		} else {
			window.addEventListener( 'load', checkSlider );
		}
	}

	/** Set up one viewer once (also used for slides that sliders clone later). */
	function initWrap( wrap ) {
		if ( wrap.wc3dReady ) {
			return;
		}
		wrap.wc3dReady = true;
		isolateGestures( wrap );
		setupControls( wrap );
		if ( ! wrap.closest( '.wc3d-gallery-slide' ) ) {
			loadWhenVisible( wrap );
		}
	}

	function initAllWraps( root ) {
		Array.prototype.forEach.call( ( root || document ).querySelectorAll( '.wc3d-wrap' ), initWrap );
	}

	/**
	 * Theme slider (Porto / Owl Carousel or similar): the injector already put our slides inside it.
	 * Track which slide is shown, load the engine for 3D slides, wire up our thumbnails.
	 */
	function setupThemeGallery( main ) {
		var root = main.closest( '.wc3d-has-model' ) || main.parentNode;
		var queued = false;

		function isShown( el ) {
			var item = el.closest( '.owl-item, .slick-slide, .swiper-slide' );
			if ( item ) {
				return item.classList.contains( 'active' ) || item.classList.contains( 'slick-active' ) || item.classList.contains( 'swiper-slide-active' );
			}
			return false;
		}

		function sync() {
			queued = false;
			initAllWraps( main ); // slides cloned by the slider (loop mode) need their controls too.

			var inSlider = !! main.querySelector( '.owl-stage, .slick-track, .swiper-wrapper' );
			if ( ! inSlider ) {
				// No slider running (stacked/grid layout): load when scrolled into view.
				Array.prototype.forEach.call( main.querySelectorAll( '.wc3d-gallery-slide' ), function ( slide ) {
					if ( ! slide.wc3dWatched ) {
						slide.wc3dWatched = true;
						loadWhenVisible( slide );
					}
				} );
				return;
			}

			var active = Array.prototype.some.call( main.querySelectorAll( '.wc3d-gallery-slide' ), isShown );
			root.classList.toggle( 'wc3d-active', active );
			document.documentElement.classList.toggle( 'wc3d-3d-active', active );
			if ( active ) {
				loadEngine();
			}
		}

		function queue() {
			if ( ! queued ) {
				queued = true;
				( window.requestAnimationFrame || setTimeout )( sync );
			}
		}

		new MutationObserver( queue ).observe( main, { attributes: true, attributeFilter: [ 'class' ], childList: true, subtree: true } );
		queue();
	}

	/** Our thumbnails in a theme slider: go to the matching 3D slide (Owl Carousel API). */
	function onThemeThumb( e ) {
		var thumb = e.target.closest && e.target.closest( '.wc3d-theme-thumb[data-wc3d-index]' );
		if ( ! thumb ) {
			return;
		}
		loadEngine();
		var main = document.querySelector( '[data-wc3d-main]' );
		var $ = window.jQuery;
		if ( ! main || ! $ || ! $( main ).data( 'owl.carousel' ) ) {
			return;
		}
		var idx = thumb.getAttribute( 'data-wc3d-index' );
		var originals = Array.prototype.slice.call( main.querySelectorAll( '.owl-stage > .owl-item:not(.cloned)' ) );
		var pos = -1;
		originals.forEach( function ( item, i ) {
			if ( item.querySelector( '.wc3d-gallery-slide[data-wc3d-index="' + idx + '"]' ) ) {
				pos = i;
			}
		} );
		if ( pos < 0 ) {
			return;
		}
		// Most themes (Porto included) already move the slider on thumbnail click. Only step in when
		// the theme did not: a second "to" during the animation confuses Owl's active slide.
		setTimeout( function () {
			var carousel = $( main ).data( 'owl.carousel' );
			if ( ! carousel ) {
				return;
			}
			var busy = typeof carousel.is === 'function' && ( carousel.is( 'animating' ) || carousel.is( 'dragging' ) );
			var current = typeof carousel.relative === 'function' ? carousel.relative( carousel.current() ) : -1;
			if ( ! busy && current !== pos ) {
				$( main ).trigger( 'to.owl.carousel', [ pos, 300, true ] );
			}
		}, 80 );
	}

	function init() {
		document.addEventListener( 'click', onThemeThumb );
		document.addEventListener( 'pointerover', function ( e ) {
			if ( e.target.closest && e.target.closest( '.wc3d-theme-thumb' ) ) {
				loadEngine();
			}
		}, { passive: true } );

		if ( ! document.querySelector( '.wc3d-wrap' ) ) {
			return;
		}

		initAllWraps();

		Array.prototype.forEach.call( document.querySelectorAll( '.woocommerce-product-gallery .wc3d-gallery-slide:not(.wc3d-theme-slide)' ), setupGallerySlide );
		Array.prototype.forEach.call( document.querySelectorAll( '[data-wc3d-main]' ), setupThemeGallery );

		if ( settings.load === 'page' ) {
			loadEngine();
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
