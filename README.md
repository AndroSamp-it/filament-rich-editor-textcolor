# filament-rich-editor-textcolor
[![Latest Version on Packagist](https://img.shields.io/packagist/v/androsamp/filament-rich-editor-textcolor.svg?style=flat-square)](https://packagist.org/packages/androsamp/filament-rich-editor-textcolor)
[![Total Downloads](https://img.shields.io/packagist/dt/androsamp/filament-rich-editor-textcolor.svg?style=flat-square)](https://packagist.org/packages/androsamp/filament-rich-editor-textcolor)

Plugin for Filament Rich Editor: text color selection (TipTap/ProseMirror).

## Installation

```bash
composer require androsamp/filament-rich-editor-textcolor
```

## Usage

```php
->toolbarButtons(['textColorPicker'])
```
```php
->floatingToolbars([
    'paragraph' => [
        'textColorPicker'
    ]
])
```
For correct display after rendering:
```php
use Androsamp\FilamentRichEditorTextColor\TextColorRichContentPlugin;
use Filament\Forms\Components\RichEditor\RichContentRenderer;

// Fetch the post and its content
$post = Post::first();

// Render the content using Filament's RichContentRenderer
$renderer = RichContentRenderer::make($post->content);

// Post-process the HTML to support text color styles
$html = $renderer->toUnsafeHtml();
$html = TextColorRichContentPlugin::make()->postProcessHtml($html);

// $html now contains the final HTML with text color support
```

## Localization

The package includes translations for English and Russian languages. The translations are automatically loaded when the package is installed.

### Available Translation Keys

```php
'filament-rich-editor-textcolor::text-color.label' // Button label
'filament-rich-editor-textcolor::text-color.modal_heading' // Modal heading
```

### Publishing Translations

To customize the translations, you can publish them to your project:

```bash
php artisan vendor:publish --tag=filament-rich-editor-textcolor-translations
```

This will copy the translation files to `resources/lang/vendor/filament-rich-editor-textcolor/`.

### Supported Languages

- **English** (`en`) - Default
- **Russian** (`ru`)

## License

MIT