<?php

namespace Androsamp\FilamentRichEditorTextColor;

use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Support\Facades\FilamentAsset;

class TextColorRichContentPluginForEditor implements RichContentPlugin
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
}

