<?php

namespace NeoPhp\Testing;

use NeoPhp\Core\Application;

/**
 * Creates Application Trait
 * 
 * Handles application creation for tests
 */
trait CreatesApplication
{
    /**
     * Creates the application
     */
    public function createApplication(): Application
    {
        $app = require __DIR__ . '/../../bootstrap/app.php';

        // Set to testing environment
        $app->environment = 'testing';

        return $app;
    }

    /**
     * Refresh the application instance
     */
    protected function refreshApplication(): void
    {
        $this->app = $this->createApplication();
    }
}
