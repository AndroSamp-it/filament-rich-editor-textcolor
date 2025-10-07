<?php

namespace Androsamp\FilamentRichEditorTextColor;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
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
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'filament-rich-editor-textcolor');

        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/filament-rich-editor-textcolor'),
        ], 'filament-rich-editor-textcolor-translations');

        FilamentAsset::register([
            Js::make('rich-content-plugins/text-color', __DIR__ . '/../resources/dist/filament/rich-content-plugins/text-color.js')->loadedOnRequest(),
            \Filament\Support\Assets\Css::make('text-color-styles', __DIR__ . '/../resources/css/text-color-styles.css'),
        ]);

        RichEditor::configureUsing(function (RichEditor $richEditor) {
            $richEditor->plugins([
                TextColorRichContentPlugin::make(),
            ]);
        });

        $this->app->resolving(RichContentRenderer::class, function (RichContentRenderer $renderer) {
            $renderer->plugins([
                TextColorRichContentPlugin::make(),
            ]);
        });
    }
}


