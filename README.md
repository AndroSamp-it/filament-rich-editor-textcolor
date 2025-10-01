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

## License

MIT