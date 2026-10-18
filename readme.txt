=== WC 3D Product Viewer ===
Contributors: sanketthakkar99
Tags: woocommerce, 3d, glb, gltf, product gallery, ar
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add one or more interactive 3D models (GLB / glTF) to a WooCommerce product. Each one is shown as its own slide at the end of the product gallery, with AR on supported phones.

== Description ==

* Upload **several** .glb / .gltf files per product from the "3D Models" box on the product edit screen (up to 20).
* Each model appears as its **own separate slide** at the end of the product image gallery, with its own thumbnail.
* Drag to reorder models, give each an optional label (e.g. "Open drawers", "Navy") shown on its thumbnail, and an optional thumbnail image.
* Each model downloads only when its slide is opened.
* Shoppers can drag to rotate, scroll / pinch to zoom, reset the view and open it full screen.
* "View in your space" AR button on Android (Scene Viewer / WebXR) and iPhone (Quick Look).
* Fast pages: the 3D engine (Google model-viewer, bundled locally) loads only when the 3D slide is opened.
* Works inside the WooCommerce gallery **and** inside themes' own sliders: Porto (Owl Carousel) is detected automatically; other custom sliders work by entering their CSS selectors.
* A "3D" column in the product list shows how many models each product has.
* Shortcode for themes and page builders with their own gallery: `[wc_3d_viewer]`.

Author: Sanket Thakkar — https://github.com/sanketthakkar99/

== Installation ==

1. Plugins → Add New → Upload Plugin, choose `wc-3d-product-viewer.zip`, then Activate.
2. Edit a product, find the **3D Models** box in the right sidebar (under Product gallery) and click **Add 3D models**.
3. Upload or pick one or more .glb / .gltf files (Ctrl/Cmd-click or Shift-click to select several), optionally add labels or thumbnails, drag to reorder, then Update the product.
4. Adjust options in **WooCommerce → Settings → Products → 3D Viewer**.

== Shortcode ==

`[wc_3d_viewer]` – every model of the current product, one after another.
`[wc_3d_viewer model="2"]` – only the 2nd model.
`[wc_3d_viewer product_id="123" model="1" height="480px" auto_rotate="no" ar="yes" bg_color="#ffffff"]`

If your theme's gallery isn't supported, set "Where to show the models" to "Only where I place the shortcode" and place the shortcode in the product template.

== Themes with their own gallery slider (Porto, …) ==

WooCommerce → Settings → Products → 3D Viewer → **Gallery type**:

* **Auto-detect** (default) uses the theme slider mode for Porto (and Porto child themes), and the standard WooCommerce gallery for everything else.
* **Theme's own slider**: the plugin copies one of the theme's slides and thumbnails, swaps the photo for the 3D viewer and adds one slide + one thumbnail per model before the theme starts its slider. Set the two selectors to your theme's markup:
  * Theme slider selector – Porto: `.product-image-slider`
  * Theme thumbnails selector – Porto: `.product-thumbs-slider, .product-thumbs-vertical-slider` (type `none` if there are no thumbnails)
* If the slider element can't be found, the models are shown right below the gallery instead.

== Developer hooks ==

* `wc3d_product_models` (array $models, WC_Product $product) – each item is `array( 'id', 'thumb', 'label' )`.
* `wc3d_model_viewer_attributes` (array $attrs, WC_Product $product, array $args, array $model) – change any <model-viewer> attribute, e.g. `camera-orbit`, `environment-image`, `ios-src`.
* `wc3d_gallery_slide_html` (string $html, WC_Product $product, array $model, int $index) – once per model.
* `wc3d_show_gallery_slide` (bool $show, WC_Product $product)
* `wc3d_gallery_slide_priority` (int, default 99)
* `wc3d_model_viewer_script_url` (string) – load model-viewer from a CDN instead.
* `wc3d_user_can_upload_models` (bool $can, int $user_id)
* `wc3d_gallery_mode` (string "woocommerce"|"theme") – force the gallery integration.
* `wc3d_theme_slider_themes` (string[] theme folder names, default `porto`) – themes auto-detected as "theme slider".
* `wc3d_theme_slider_priority` (int, default 25) – priority on `woocommerce_before_single_product_summary` for the theme slider output.

== Tips ==

* Prefer .glb: one file with textures inside. A .gltf must have its buffers and textures embedded (data URIs).
* Keep models under ~5–10 MB for quick loading (compress with gltf.report or gltf-transform).
* If uploads fail, your host's upload_max_filesize may be too small.

== Credits ==

Bundles Google's <model-viewer> 4.4.0 (Apache License 2.0) – see assets/vendor/model-viewer/LICENSE.

== Changelog ==

= 1.2.0 =
* 3D slides inside themes' own gallery sliders: Porto (Owl Carousel) auto-detected, custom selectors for other themes.
* Works with Owl loop mode (cloned slides), theme thumbnail syncing, and sliders that start early.
* Zoom/lightbox buttons are hidden while a 3D slide is shown.

= 1.1.0 =
* Multiple 3D models per product, each shown as a separate gallery slide.
* Per-model label and thumbnail, drag-and-drop ordering.
* Shortcode `model` attribute.
* Products saved with 1.0 keep working and are converted on their next save.

= 1.0.0 =
* First release.
