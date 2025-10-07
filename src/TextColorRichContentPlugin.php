<?php

namespace Androsamp\FilamentRichEditorTextColor;

use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Support\Facades\FilamentAsset;

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
        return [];
    }

    /**
     * @return array<RichEditorTool>
     */
    public function getEditorTools(): array
    {
        return [
            RichEditorTool::make('textColorPicker')
                ->label(__('filament-rich-editor-textcolor::text-color.label'))
                ->icon('heroicon-o-paint-brush')
                ->action(arguments: '{ color: $getEditor()?.getAttributes(\'textColor\')?.[\'data-color\'] ?? null }'),
        ];
    }

    /**
     * @return array<Action>
     */
    public function getEditorActions(): array
    {
        return [
            Action::make('textColorPicker')
                ->modalWidth('lg')
                ->modalHeading(__('filament-rich-editor-textcolor::text-color.modal_heading'))
                ->fillForm(fn (array $arguments): array => [
                    'color' => $arguments['color'] ?? null,
                ])
                ->schema([
                    ColorPicker::make('color'),
                ])
                ->action(function (array $arguments, array $data, RichEditor $component): void {
                    $component->runCommands(
                        [
                            EditorCommand::make('setTextColor', arguments: [[
                                'color' => $data['color'] ?? null,
                            ]]),
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

        // Helper: find the innermost element that actually contains non-empty text
        $findInnermostContainingText = null;
        $findInnermostContainingText = function (\DOMElement $el) use (&$findInnermostContainingText) {
            // If this element directly has a non-empty text node, return it
            foreach ($el->childNodes as $child) {
                if ($child instanceof \DOMText && trim($child->nodeValue) !== '') {
                    return $el;
                }
            }

            // Otherwise, recurse into element children and return the first matching descendant
            foreach ($el->childNodes as $child) {
                if ($child instanceof \DOMElement) {
                    $found = $findInnermostContainingText($child);
                    if ($found) {
                        return $found;
                    }
                }
            }

            return null;
        };

        // 1) Move color from <span ...> (including data-color / --color vars) to nested <mark>,
        //    and also apply color to the innermost element that contains the text
        $spans = $xpath->query('//span[(@data-color) or contains(translate(@style, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "color:") or contains(translate(@style, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "--color")]');
        $spansToUnwrap = [];
        /** @var \DOMElement $span */
        foreach ($spans as $span) {
            $styles = $parseStyle($span->getAttribute('style'));
            $color = $styles['color'] ?? null;

            // support --color CSS variable
            if (! $color && isset($styles['--color'])) {
                $color = $styles['--color'];
            }

            // support data-color attribute
            if (! $color) {
                $attrColor = $span->getAttribute('data-color');
                if ($attrColor !== '') {
                    $color = $attrColor;
                }
            }

            if (! $color) {
                continue;
            }

            $marks = $xpath->query('.//mark', $span);
            $updatedAny = false;
            /** @var \DOMElement $mark */
            foreach ($marks as $mark) {
                // Apply color to the mark itself
                $markStyles = $parseStyle($mark->getAttribute('style'));
                $markStyles['color'] = $color;
                $mark->setAttribute('style', $styleToString($markStyles));

                // Apply color to all ancestor inline elements up to the enclosing span
                /** @var \DOMNode|null $parent */
                $parent = $mark->parentNode;
                while ($parent instanceof \DOMElement && $parent !== $span) {
                    /** @var \DOMElement $parentElement */
                    $parentElement = $parent;
                    $parentStyles = $parseStyle($parentElement->getAttribute('style'));
                    $parentStyles['color'] = $color;
                    $parentElement->setAttribute('style', $styleToString($parentStyles));
                    $parent = $parentElement->parentNode;
                }

                // As a fallback, also ensure the innermost element containing text has the color
                $innermost = $findInnermostContainingText($mark);
                if ($innermost instanceof \DOMElement) {
                    $innermostStyles = $parseStyle($innermost->getAttribute('style'));
                    $innermostStyles['color'] = $color;
                    $innermost->setAttribute('style', $styleToString($innermostStyles));
                }

                $updatedAny = true;
            }

            if ($updatedAny) {
                // Keep color on both span and mark/innermost element
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
