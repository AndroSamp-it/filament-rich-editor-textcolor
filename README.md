# filament-rich-editor-textcolor

Plugin for Filament Rich Editor: text color selection (TipTap/ProseMirror).

## Installation

```bash
composer require AndroSamp-it/filament-rich-editor-textcolor
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
