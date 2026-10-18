# WC 3D Product Viewer

Add interactive 3D product models to WooCommerce product galleries with support for multiple GLB/glTF models, customizable thumbnails, and augmented reality (AR) on compatible devices.

<p align="center">
  <img src="https://img.shields.io/badge/WordPress-6.0%2B-21759B?logo=wordpress&logoColor=white" alt="WordPress 6.0+">
  <img src="https://img.shields.io/badge/WooCommerce-Compatible-96588A?logo=woocommerce&logoColor=white" alt="WooCommerce compatible">
  <img src="https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white" alt="PHP 7.4+">
  <img src="https://img.shields.io/badge/Version-1.2.0-blue" alt="Version 1.2.0">
  <img src="https://img.shields.io/badge/License-GPL--2.0%2B-green" alt="GPL v2 or later">
</p>

## Overview

**WC 3D Product Viewer** enhances WooCommerce product pages by allowing customers to interact with products through 3D models.

Store owners can upload multiple 3D models to a single product. Each model appears as a separate slide at the end of the product gallery, complete with its own thumbnail and optional label.

Customers can rotate, zoom, reset, and explore supported models in augmented reality, helping them understand products before purchasing.

## Features

- **Multiple 3D Models:** Add up to 20 GLB/glTF models to a single product.
- **Product Gallery Integration:** Display each model as an individual gallery slide.
- **Custom Thumbnails:** Assign a separate thumbnail image to each model.
- **Model Labels:** Add descriptive labels such as "Open Drawers", "Navy", or "Side View".
- **Drag-and-Drop Ordering:** Easily change the order of 3D models.
- **Interactive Controls:** Rotate, zoom, reset the camera view, and open models in full screen.
- **Augmented Reality:** Support Android Scene Viewer, WebXR, and compatible iOS Quick Look workflows.
- **Lazy Loading:** Load model assets when their slides are opened.
- **Optimized JavaScript Loading:** Load the bundled Google `<model-viewer>` library only when required.
- **Theme Compatibility:** Integrate with the standard WooCommerce gallery and supported theme-specific sliders.
- **Porto Support:** Automatically detect supported Porto Owl Carousel configurations.
- **Custom Slider Integration:** Configure CSS selectors for other theme galleries.
- **Product List Indicator:** Display a 3D column showing the number of models assigned to each product.
- **Shortcode Support:** Embed models in supported custom templates and page-builder layouts.

## Requirements

| Requirement | Version |
|---|---|
| WordPress | 6.0 or later |
| PHP | 7.4 or later |
| WooCommerce | Installed and activated |
| 3D model formats | GLB and glTF |

The plugin is marked as tested up to WordPress 7.1 in its current metadata. Compatibility with later versions should be verified through testing.

## Installation

### Method 1: Upload the Plugin

1. Download or package the plugin as `wc-3d-product-viewer.zip`.
2. Open your WordPress dashboard.
3. Navigate to **Plugins → Add New Plugin → Upload Plugin**.
4. Upload the ZIP file and click **Install Now**.
5. Activate the plugin.
6. Ensure WooCommerce is installed and activated.

### Method 2: Install from the Repository

Clone the repository into your WordPress plugins directory:

```bash
cd wp-content/plugins
git clone https://github.com/sanketthakkar99/wc-3d-product-viewer.git
```

> Replace the repository URL with the actual GitHub repository URL if its name or location differs.

Activate **WC 3D Product Viewer** from the WordPress dashboard.

### Add 3D Models to a Product

1. Edit a WooCommerce product.
2. Locate the **3D Models** box in the product editing interface.
3. Click **Add 3D Models**.
4. Upload or select one or more GLB/glTF files from the Media Library.
5. Add optional labels and thumbnail images.
6. Drag the models into the desired order.
7. Update the product.
8. Preview the product page to test the gallery and viewer.

## Supported 3D Formats

### GLB — Recommended

GLB packages model geometry, materials, and textures into a single binary file. It is generally the easiest format to manage and distribute.

### glTF

glTF models are supported when their required buffers and textures are embedded as data URIs, according to the plugin's documented requirements.

### Augmented Reality

The plugin provides an AR viewing option on compatible devices.

- **Android:** Scene Viewer and WebXR, where supported.
- **iOS:** Quick Look with a compatible USDZ asset where required.

AR availability depends on device, browser, model format, and platform capabilities. A GLB/glTF model does not automatically provide a compatible iOS Quick Look asset.

## Shortcodes

Use the `[wc_3d_viewer]` shortcode to display 3D models associated with a WooCommerce product.

### Display All Models

```text
[wc_3d_viewer]
```

Displays all models associated with the current product, one after another.

### Display a Specific Model

```text
[wc_3d_viewer model="2"]
```

Displays only the second model associated with the product.

### Customize the Viewer

```text
[wc_3d_viewer product_id="123" model="1" height="480px" auto_rotate="no" ar="yes" bg_color="#ffffff"]
```

| Attribute | Description | Example |
|---|---|---|
| `product_id` | WooCommerce product ID | `123` |
| `model` | Model position, starting from 1 | `1` |
| `height` | Viewer height | `480px` |
| `auto_rotate` | Enable or disable automatic rotation | `no` |
| `ar` | Enable or disable the AR option | `yes` |
| `bg_color` | Viewer background color | `#ffffff` |

These examples reflect the documented shortcode interface. Confirm attribute support against the installed plugin version before relying on individual options.

### Page Builders and Custom Templates

If your theme or page builder uses its own gallery, set **Where to show the models** to **Only where I place the shortcode**.

Then insert the shortcode into the appropriate single-product template or supported shortcode widget.

## Theme Slider Integration

Navigate to:

**WooCommerce → Settings → Products → 3D Viewer**

### Gallery Type: Auto-detect

The default mode:

- Detects supported theme-specific gallery configurations, including Porto.
- Uses the standard WooCommerce gallery for other configurations.
- Falls back to displaying models below the gallery when the theme slider cannot be located.

### Gallery Type: Theme's Own Slider

Use this mode when your theme manages its own product gallery.

Configure the selectors to match your theme's markup.

**Example Porto selectors:**

| Setting | CSS selector |
|---|---|
| Theme slider selector | `.product-image-slider` |
| Theme thumbnails selector | `.product-thumbs-slider, .product-thumbs-vertical-slider` |

These are example selectors and may require adjustment for customized themes.

If your theme has no thumbnail slider, enter `none` in the thumbnail selector setting.

### Compatibility Notes

- Compatible Owl Carousel configurations can support loop mode and cloned slides.
- Thumbnail synchronization is supported where the theme integration permits it.
- Custom slider initialization and markup can affect compatibility.
- If the configured slider cannot be found, the plugin falls back to output below the product gallery.

## Developer Hooks

The plugin exposes filters for customizing model data, viewer attributes, gallery markup, permissions, and theme integration.

### `wc3d_product_models`

Filter the models associated with a WooCommerce product.

Parameters:

- `$models` — Array of product models.
- `$product` — `WC_Product` object.

Each model includes fields such as `id`, `thumb`, and `label`.

### `wc3d_model_viewer_attributes`

Customize the attributes passed to the `<model-viewer>` element.

Parameters:

- `$attrs` — Viewer attributes.
- `$product` — `WC_Product` object.
- `$args` — Viewer arguments.
- `$model` — Current model data.

Possible customization includes `camera-orbit`, `environment-image`, and `ios-src`.

### `wc3d_gallery_slide_html`

Customize the HTML generated for each 3D gallery slide.

Parameters:

- `$html` — Generated slide HTML.
- `$product` — `WC_Product` object.
- `$model` — Current model data.
- `$index` — Model index.

### `wc3d_show_gallery_slide`

Control whether a 3D gallery slide should be displayed.

Parameters:

- `$show` — Boolean visibility flag.
- `$product` — `WC_Product` object.

### `wc3d_gallery_slide_priority`

Customize the priority used for gallery slide output.

Default: `99`.

### `wc3d_model_viewer_script_url`

Customize the URL used to load the model-viewer JavaScript library.

By default, the plugin uses its bundled library.

### `wc3d_user_can_upload_models`

Customize whether a user can upload or manage 3D models.

Parameters:

- `$can` — Permission result.
- `$user_id` — WordPress user ID.

### `wc3d_gallery_mode`

Force a gallery integration mode.

Supported values:

- `woocommerce`
- `theme`

### `wc3d_theme_slider_themes`

Customize the theme folder names recognized for automatic theme-slider integration.

Default: `porto`.

### `wc3d_theme_slider_priority`

Customize the priority of theme-slider output on `woocommerce_before_single_product_summary`.

Default: `25`.

> Refer to the plugin source code for exact callback signatures, accepted values, and usage examples before implementing custom integrations.

## Performance Recommendations

For a smoother 3D viewing experience:

- Prefer GLB files for simpler asset management.
- Aim for model files smaller than approximately 5–10 MB when practical.
- Optimize geometry, materials, and texture resolution.
- Use [gltf.report](https://gltf.report/) or [gltf-transform](https://gltf-transform.dev/) to inspect and optimize models.
- Test on mobile devices and slower network connections.
- Avoid unnecessarily large texture maps and excessive geometry.

Actual performance depends on model complexity, hosting, browser, device, and network conditions.

## Troubleshooting

### Models are not uploading

Check the following:

- Your hosting provider's file upload restrictions.
- PHP `upload_max_filesize` and `post_max_size`.
- The selected file format and file size.
- WordPress Media Library permissions.

### Models are not appearing in the gallery

- Confirm that the product has been updated.
- Verify the selected gallery integration mode.
- Check the configured theme slider and thumbnail selectors.
- Test the standard WooCommerce gallery mode.
- Review browser console errors and any JavaScript conflicts.

### Models load slowly

Optimize model geometry and textures, reduce file size, and test the viewer on a different device or network.

### AR is unavailable

Confirm that the device and browser support the relevant AR workflow. For iOS Quick Look, verify that a compatible USDZ asset is available when required.

## Frequently Asked Questions

**Can I add multiple models to one product?**

Yes. The plugin supports up to 20 models per product.

**Can each model have a different thumbnail and label?**

Yes. Both are optional and can be configured independently for each model.

**Does the plugin support Elementor?**

You can use the shortcode in supported shortcode widgets or custom product templates.

**Does it work with Porto?**

The plugin includes automatic detection for supported Porto Owl Carousel configurations. Customized layouts may require selector adjustments.

**Does the plugin load every model immediately?**

The plugin is designed to load model assets when their slides are opened.

## Dependencies and Credits

This plugin bundles Google's [`<model-viewer>`](https://modelviewer.dev/) version 4.4.0.

The bundled library is distributed under the Apache License 2.0. See `assets/vendor/model-viewer/LICENSE` for license details.

## Changelog

### 1.2.0

- Added integration with theme-specific product gallery sliders.
- Added automatic detection for supported Porto Owl Carousel configurations.
- Added configurable CSS selectors for custom theme sliders.
- Improved compatibility with Owl Carousel loop mode and cloned slides.
- Improved synchronization between theme thumbnails and 3D model slides.
- Improved compatibility with sliders that initialize early.
- Hid zoom and lightbox buttons while a 3D slide is active.

### 1.1.0

- Added support for multiple 3D models per product.
- Added individual gallery slides for each model.
- Added per-model labels and thumbnail images.
- Added drag-and-drop model ordering.
- Added the shortcode `model` attribute.
- Preserved compatibility with products saved using version 1.0, with conversion on the next product save.

### 1.0.0

- Initial release.

## Contributing

Bug reports, feature requests, and code contributions are welcome.

1. Fork the repository.
2. Create a feature branch.
3. Make your changes and test them.
4. Submit a pull request with a clear description of the changes.

For bugs, include your WordPress version, WooCommerce version, PHP version, active theme, and steps to reproduce the issue.

## Author

**Sanket Thakkar**

- GitHub: [@sanketthakkar99](https://github.com/sanketthakkar99/)

## License

WC 3D Product Viewer is licensed under the **GNU General Public License v2.0 or later**.

See the [GNU GPL v2 license](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html).

