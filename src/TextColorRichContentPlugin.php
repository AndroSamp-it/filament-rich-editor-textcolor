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
        return [];
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
        return [];
    }

    /**
     * @return array<Action>
     */
    public function getEditorActions(): array
    {
        return [];
    }
}


