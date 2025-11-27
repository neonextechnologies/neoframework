<?php

namespace NeoPhp\Debug;

use NeoPhp\Http\Request;
use NeoPhp\Http\Response;

class DebugBarMiddleware
{
    protected DebugBar $debugBar;

    public function __construct(DebugBar $debugBar)
    {
        $this->debugBar = $debugBar;
    }

    public function handle(Request $request, callable $next): Response
    {
        $response = $next($request);

        // Only inject in debug mode and for HTML responses
        if (app('config')->get('app.debug', false)) {
            $response = $this->debugBar->injectDebugBar($response);
        }

        return $response;
    }
}
