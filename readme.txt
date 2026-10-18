=== WC 3D Product Viewer ===
Contributors: sanketthakkar99
Tags: woocommerce, 3d, glb, gltf, product gallery, augmented reality, ar, product viewer
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add interactive 3D product models to WooCommerce product galleries, with multiple GLB/glTF models per product and augmented reality (AR) support on compatible devices.

== Description ==

WC 3D Product Viewer enhances your WooCommerce product pages by allowing customers to explore products using interactive 3D models.

Upload one or more 3D models to a product and display each model as a separate slide in the product gallery. Customers can rotate, zoom, and view supported models in augmented reality to better understand your products before purchasing.

= Key Features =

* **Multiple 3D Models:** Add up to 20 GLB or glTF models to a single WooCommerce product.
* **Gallery Integration:** Display each model as a separate slide at the end of the product image gallery.
* **Custom Thumbnails:** Assign an optional thumbnail image to each 3D model.
* **Model Labels:** Add labels such as "Open Drawers", "Navy", or "Side View" to identify individual models.
* **Drag-and-Drop Ordering:** Reorder models directly from the product editing screen.
* **Interactive 3D Controls:** Allow customers to rotate, zoom, reset the camera view, and open models in full-screen mode.
* **Augmented Reality:** Provide a "View in your space" experience on supported Android and iOS devices.
* **Lazy Loading:** Load a model only when its gallery slide is opened.
* **Optimized Asset Loading:** Load the bundled Google model-viewer engine only when required.
* **Theme Slider Compatibility:** Support the standard WooCommerce product gallery and compatible theme-specific sliders, including Porto's Owl Carousel.
* **Custom Slider Selectors:** Configure CSS selectors to integrate with other themes' product gallery sliders.
* **Product List Indicator:** Display a 3D column in the WooCommerce product list showing the number of models assigned to each product.
* **Shortcode Support:** Display 3D models in custom product templates, page builders, and other supported layouts.

= Supported Model Formats =

* `.glb` — Recommended for most use cases.
* `.gltf` — Supported when the model's required resources, buffers, and textures are embedded as data URIs.

= Augmented Reality Support =

The plugin provides an AR viewing option on compatible devices and browsers:

* **Android:** Scene Viewer and WebXR, where supported.
* **iOS:** Quick Look using a compatible USDZ model.

AR availability depends on the device, browser, model format, and platform capabilities. GLB/glTF models do not automatically guarantee iOS Quick Look compatibility; provide a compatible USDZ asset where required.

= Theme Compatibility =

The plugin supports the standard WooCommerce product gallery and theme-specific gallery sliders.

Porto's Owl Carousel integration is detected automatically in supported configurations. Other themes can be configured using their gallery and thumbnail CSS selectors.

If the theme slider cannot be detected, the plugin falls back to displaying the 3D models below the product gallery.

== Installation ==

1. Download the `wc-3d-product-viewer.zip` plugin package.
2. In your WordPress dashboard, navigate to **Plugins → Add New Plugin → Upload Plugin**.
3. Upload the ZIP file and click **Install Now**.
4. Click **Activate Plugin**.
5. Ensure WooCommerce is installed and activated.
6. Edit a WooCommerce product and locate the **3D Models** box in the product editing interface.
7. Click **Add 3D Models** and upload or select your GLB/glTF files from the WordPress Media Library.
8. Add optional labels and thumbnail images, then drag the models into the desired order.
9. Update the product to save your changes.
10. Configure the plugin under **WooCommerce → Settings → Products → 3D Viewer**.

= Requirements =

* WordPress 6.0 or later.
* PHP 7.4 or later.
* WooCommerce installed and activated.
* Valid GLB/glTF files compatible with Google's model-viewer component.

== Shortcodes ==

Use the `[wc_3d_viewer]` shortcode to display 3D models associated with a WooCommerce product.

= Display All Models =

`[wc_3d_viewer]`

Displays all 3D models associated with the current product, one after another.

= Display a Specific Model =

`[wc_3d_viewer model="2"]`

Displays only the second model associated with the current product.

= Customize the Viewer =

`[wc_3d_viewer product_id="123" model="1" height="480px" auto_rotate="no" ar="yes" bg_color="#ffffff"]`

Available shortcode attributes:

* `product_id` — ID of the WooCommerce product whose models should be displayed.
* `model` — Model position to display, starting from 1.
* `height` — Desired viewer height, such as `480px`.
* `auto_rotate` — Enable or disable automatic rotation using `yes` or `no`.
* `ar` — Enable or disable the AR option using `yes` or `no`.
* `bg_color` — Viewer background color, such as `#ffffff`.

Shortcode attributes depend on the plugin's implementation and version. Confirm the supported values in the installed version if customizing further.

= Using the Shortcode with Custom Templates =

If your theme or page builder uses its own product gallery, configure the plugin's **Where to show the models** setting to **Only where I place the shortcode**.

Then add the shortcode to the appropriate single-product template or page-builder layout.

== Theme Slider Integration ==

Navigate to:

**WooCommerce → Settings → Products → 3D Viewer**

Configure the **Gallery type** setting to determine how 3D models are displayed.

= Auto-detect =

This is the default setting.

* Detects supported theme-specific gallery configurations, including Porto's Owl Carousel.
* Uses the standard WooCommerce product gallery for other configurations.
* Falls back to displaying models below the gallery if the theme slider cannot be found.

= Theme's Own Slider =

Select this option when the theme uses its own product gallery slider.

The plugin uses the theme's slide and thumbnail structure to insert a separate 3D viewer slide and thumbnail for each model.

Configure the following CSS selectors:

* **Theme slider selector:** `.product-image-slider`
* **Theme thumbnails selector:** `.product-thumbs-slider, .product-thumbs-vertical-slider`

These are example selectors for Porto themes. Your theme may use different selectors.

If the theme does not have a thumbnail slider, enter `none` in the thumbnail selector setting.

= Slider Compatibility Notes =

* The plugin supports integration with compatible Owl Carousel configurations, including loop mode and cloned slides.
* Theme thumbnails are synchronized with the corresponding 3D model slides where supported.
* Slider initialization timing may affect compatibility with some custom themes.
* If the configured slider element cannot be located, the plugin displays the models below the product gallery.

== Frequently Asked Questions ==

= Can I add multiple 3D models to one product? =

Yes. You can add up to 20 models to a single WooCommerce product. Each model appears as a separate gallery slide.

= Which 3D file format should I use? =

GLB is recommended because it can package geometry, materials, and textures into a single file.

glTF is also supported when its required resources are embedded as data URIs.

= Can I customize model thumbnails and labels? =

Yes. Each model can have an optional thumbnail image and label. You can also reorder models using drag-and-drop controls.

= Does the plugin support augmented reality? =

Yes. The plugin provides an AR viewing option on compatible Android and iOS devices. Actual availability depends on device and browser support. iOS Quick Look may require a compatible USDZ file.

= Does it work with the Porto theme? =

The plugin includes automatic detection for supported Porto Owl Carousel configurations. Other theme versions or customized layouts may require selector adjustments.

= Can I use the plugin with Elementor or another page builder? =

Yes. Use the `[wc_3d_viewer]` shortcode in a supported shortcode widget or custom product template.

= Why is my 3D model loading slowly? =

Large models and textures can increase loading time. Optimize your model, reduce texture sizes, and keep files as small as practical.

= Why is my model not uploading? =

Check your WordPress upload limits, including `upload_max_filesize` and `post_max_size`, along with your hosting provider's file restrictions and the file format.

= Does the plugin load all 3D models immediately? =

The plugin is designed to load a model when its slide is opened, rather than loading every model immediately.

== Developer Hooks ==

The plugin provides WordPress filters for developers who need to customize model data, viewer attributes, gallery markup, permissions, and theme integration.

= `wc3d_product_models` =

Filter the models associated with a WooCommerce product.

Parameters:

* `$models` — Array of product models.
* `$product` — WooCommerce product object.

Each model contains fields such as `id`, `thumb`, and `label`.

= `wc3d_model_viewer_attributes` =

Modify the attributes passed to the `<model-viewer>` element.

Parameters:

* `$attrs` — Viewer attributes.
* `$product` — WooCommerce product object.
* `$args` — Viewer arguments.
* `$model` — Current model data.

Example use cases include customizing `camera-orbit`, `environment-image`, and `ios-src`.

= `wc3d_gallery_slide_html` =

Customize the HTML generated for an individual 3D gallery slide.

Parameters:

* `$html` — Generated slide HTML.
* `$product` — WooCommerce product object.
* `$model` — Current model data.
* `$index` — Model index.

= `wc3d_show_gallery_slide` =

Control whether the plugin displays a 3D gallery slide.

Parameters:

* `$show` — Whether the slide should be displayed.
* `$product` — WooCommerce product object.

= `wc3d_gallery_slide_priority` =

Change the priority used to position the gallery slide output.

Default: `99`.

= `wc3d_model_viewer_script_url` =

Customize the URL used to load the model-viewer JavaScript library.

By default, the plugin uses its bundled version.

= `wc3d_user_can_upload_models` =

Customize whether a user is allowed to upload or manage 3D models.

Parameters:

* `$can` — Permission result.
* `$user_id` — WordPress user ID.

= `wc3d_gallery_mode` =

Force a specific gallery integration mode.

Supported values:

* `woocommerce`
* `theme`

= `wc3d_theme_slider_themes` =

Customize the theme folder names recognized for automatic theme-slider integration.

Default: `porto`.

= `wc3d_theme_slider_priority` =

Customize the priority of the theme-slider output on `woocommerce_before_single_product_summary`.

Default: `25`.

Refer to the plugin source code for exact callback signatures, accepted values, and usage examples.

== Performance Tips ==

* Prefer GLB files to simplify asset management.
* Aim for model files smaller than approximately 5–10 MB when practical.
* Optimize geometry, textures, and materials before uploading.
* Use tools such as gltf.report or gltf-transform to inspect and optimize your models.
* Test model loading on mobile devices and slower network connections.
* Avoid unnecessarily large texture maps and complex geometry.

Actual loading performance depends on the model, hosting environment, device, browser, and network connection.

== Credits ==

This plugin bundles Google's `<model-viewer>` version 4.4.0.

The bundled library is distributed under the Apache License 2.0. See `assets/vendor/model-viewer/LICENSE` for the applicable license information.

== Changelog ==

= 1.2.0 =
* Added integration with theme-specific product gallery sliders.
* Added automatic detection for supported Porto Owl Carousel configurations.
* Added configurable CSS selectors for custom theme sliders.
* Improved compatibility with Owl Carousel loop mode and cloned slides.
* Improved synchronization between theme thumbnails and 3D model slides.
* Improved compatibility with sliders that initialize early.
* Hid zoom and lightbox buttons while a 3D slide is active.

= 1.1.0 =
* Added support for multiple 3D models per product.
* Added individual gallery slides for each model.
* Added per-model labels and thumbnail images.
* Added drag-and-drop model ordering.
* Added the shortcode `model` attribute.
* Preserved compatibility with products saved using version 1.0, with conversion on the next product save.

= 1.0.0 =
* Initial release.

== Author ==

Sanket Thakkar

Website: https://github.com/sanketthakkar99/

== License ==

WC 3D Product Viewer is licensed under the GNU General Public License, version 2 or later.

License details: https://www.gnu.org/licenses/gpl-2.0.html
