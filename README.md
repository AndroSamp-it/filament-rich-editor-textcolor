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
->toolbarButtons(['textColor'])
```
```php
->floatingToolbars([
    'paragraph' => [
        'textColor'
    ]
])
```
For correct display after rendering:
```php
use Androsamp\FilamentRichEditorTextColor\TextColorRichContentPlugin;

$renderedHtml = TextColorRichContentPlugin::make()->postProcessHtml($renderedHtml);
```

## License

MIT
