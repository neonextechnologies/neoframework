<?php

namespace NeoPhp\Translation;

use NeoPhp\Container\Container;

class TranslationServiceProvider
{
    public function register(Container $container): void
    {
        $container->singleton(Translator::class, function ($container) {
            $loader = new FileLoader($container->get('config')->get('app.lang_path', base_path('resources/lang')));
            
            $translator = new Translator(
                $loader,
                $container->get('config')->get('app.locale', 'en')
            );
            
            $translator->setFallback($container->get('config')->get('app.fallback_locale', 'en'));
            
            return $translator;
        });
    }
}
