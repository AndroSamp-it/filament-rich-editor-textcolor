<?php

namespace Androsamp\FilamentRichEditorTextColor;

use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Support\Facades\FilamentAsset;
use Tiptap\Extensions\Color as PhpColor;
use Tiptap\Marks\TextStyle as PhpTextStyle;

class TextColorRichContentPlugin implements RichContentPlugin
{
    public function getId(): string
    {
        return 'textColor';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * @return array<string>
     */
    public function getTipTapJsExtensions(): array
    {
        return [
            FilamentAsset::getScriptSrc('rich-content-plugins/text-color'),
        ];
    }

    /**
     * @return array<object>
     */
    public function getTipTapPhpExtensions(): array
    {
        return [
            app(PhpTextStyle::class),
            app(PhpColor::class),
        ];
    }

    /**
     * @return array<RichEditorTool>
     */
    public function getEditorTools(): array
    {
        return [
            RichEditorTool::make('textColor')
                ->label(__('custom-rich-editor-text-color::text-color.label'))
                ->icon('heroicon-o-paint-brush')
                ->action(arguments: '{ color: $getEditor()?.getAttributes(\'textStyle\')?.[\'color\'] }'),
        ];
    }

    /**
     * @return array<Action>
     */
    public function getEditorActions(): array
    {
        return [
            Action::make('textColor')
                ->modalWidth('lg')
                ->modalHeading(__('custom-rich-editor-text-color::text-color.modal_heading'))
                ->fillForm(fn (array $arguments): array => [
                    'color' => $arguments['color'] ?? null,
                ])
                ->schema([
                    ColorPicker::make('color'),
                ])
                ->action(function (array $arguments, array $data, RichEditor $component): void {
                    $component->runCommands(
                        [
                            EditorCommand::make('setColor', arguments: [$data['color'] ?? null]),
                        ],
                        editorSelection: $arguments['editorSelection'] ?? null,
                    );
                }),
        ];
    }

    /**
     * Post-process HTML for correct combination of text color and highlighting (mark).
     */
    public function postProcessHtml(string $html): string
    {
        $trimmed = trim($html);
        if ($trimmed === '') {
            return $html;
        }

        // Use DOM for correct work with nested inline wrappers (strong/b/em/i/u/s/code/a/span)
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $internalErrors = libxml_use_internal_errors(true);

        // Wrap in auxiliary container to preserve original structure
        $wrapped = '<div id="__tcrc_root__">' . $html . '</div>';
        @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);

        $xpath = new \DOMXPath($doc);

        // Helper functions for parsing/building style
        $parseStyle = function (?string $style): array {
            $result = [];
            if (! $style) {
                return $result;
            }
            foreach (explode(';', $style) as $chunk) {
                $chunk = trim($chunk);
                if ($chunk === '') {
                    continue;
                }
                $parts = explode(':', $chunk, 2);
                if (count($parts) !== 2) {
                    continue;
                }
                $prop = strtolower(trim($parts[0]));
                $val = trim($parts[1]);
                if ($prop !== '') {
                    $result[$prop] = $val;
                }
            }
            return $result;
        };
        $styleToString = function (array $styles): string {
            $pairs = [];
            foreach ($styles as $k => $v) {
                if ($v === '' || $v === null) {
                    continue;
                }
                $pairs[] = $k . ': ' . $v;
            }
            return implode('; ', $pairs);
        };

        $unwrapElement = function (\DOMElement $el) {
            $parent = $el->parentNode;
            if (! $parent) {
                return;
            }
            while ($el->firstChild) {
                $parent->insertBefore($el->firstChild, $el);
            }
            $parent->removeChild($el);
        };

        // 1) Move color from <span style="color:..."></span> to nested <mark>, then unwrap such <span>
        $spans = $xpath->query('//span[contains(translate(@style, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "color:")]');
        $spansToUnwrap = [];
        /** @var \DOMElement $span */
        foreach ($spans as $span) {
            $styles = $parseStyle($span->getAttribute('style'));
            $color = $styles['color'] ?? null;
            if (! $color) {
                continue;
            }

            $marks = $xpath->query('.//mark', $span);
            $updatedAny = false;
            /** @var \DOMElement $mark */
            foreach ($marks as $mark) {
                $markStyles = $parseStyle($mark->getAttribute('style'));
                $markStyles['color'] = $color; // apply text color to mark
                $mark->setAttribute('style', $styleToString($markStyles));
                $updatedAny = true;
            }

            if ($updatedAny) {
                // Keep color on both span and mark
            }
        }
        // Don't unwrap outer <span> to preserve its styles

        // 2) If <mark> contains <span style="color:..."> inside, move color to mark and unwrap such span
        $marks = $xpath->query('//mark');
        /** @var \DOMElement $mark */
        foreach ($marks as $mark) {
            $innerColorSpans = $xpath->query('.//span[contains(translate(@style, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "color:")]', $mark);
            if ($innerColorSpans->length === 0) {
                continue;
            }

            $markStyles = $parseStyle($mark->getAttribute('style'));
            // If background is not set, add yellow as default (as before)
            if (! isset($markStyles['background-color'])) {
                $markStyles['background-color'] = '#ffff00';
            }

            // Move the last found color inside mark
            $lastColor = null;
            /** @var \DOMElement $colorSpan */
            foreach ($innerColorSpans as $colorSpan) {
                $spanStyles = $parseStyle($colorSpan->getAttribute('style'));
                if (isset($spanStyles['color'])) {
                    $lastColor = $spanStyles['color'];
                }
            }
            if ($lastColor) {
                $markStyles['color'] = $lastColor;
            }
            $mark->setAttribute('style', $styleToString($markStyles));

            // Leave inner <span> unchanged so color remains on them
        }

        // Return innerHTML of root container
        $root = $doc->getElementById('__tcrc_root__');
        if (! $root) {
            return $html;
        }
        $result = '';
        foreach ($root->childNodes as $child) {
            $result .= $doc->saveHTML($child);
        }
        return $result;
    }
}
