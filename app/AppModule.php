<?php

namespace App;

use NeoPhp\Core\Attributes\Module;
use App\Providers\AppServiceProvider;

/**
 * AppModule - Root Application Module
 * 
 * This is the main module of NeoFramework application.
 * It follows the modular architecture pattern from Neonex Core.
 * 
 * @see https://neonex.co.th/docs/modules
 */
#[Module(
    imports: [],
    controllers: [],
    providers: [
        AppServiceProvider::class,
    ]
)]
class AppModule
{
    /**
     * Initialize the application module
     */
    public function __construct()
    {
        // Module initialization logic
    }

    /**
     * Bootstrap the module
     */
    public function boot(): void
    {
        // Module boot logic
    }
}
