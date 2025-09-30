<?php

namespace Androsamp\FilamentRichEditorTextColor;

use Filament\Forms\Components\RichEditor;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;

class FilamentRichEditorTextColorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'custom-rich-editor-text-color');

        FilamentAsset::register([
            Js::make('rich-content-plugins/text-color', __DIR__ . '/../resources/dist/filament/rich-content-plugins/text-color.js')->loadedOnRequest(),
            \Filament\Support\Assets\Css::make('text-color-styles', __DIR__ . '/../resources/css/text-color-styles.css'),
        ]);

        RichEditor::configureUsing(function (RichEditor $richEditor) {
            $richEditor->plugins([
                TextColorRichContentPluginForEditor::make(),
            ]);
        });
    }
}


