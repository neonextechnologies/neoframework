<?php

namespace NeoPhp\Testing;

use NeoPhp\Http\Request;
use NeoPhp\Http\Response;

/**
 * Makes HTTP Requests Trait
 * 
 * Provides HTTP testing helpers
 */
trait MakesHttpRequests
{
    protected ?Response $response = null;

    /**
     * Visit the given URI with a GET request
     */
    protected function get(string $uri, array $headers = []): Response
    {
        return $this->call('GET', $uri, [], $headers);
    }

    /**
     * Visit the given URI with a POST request
     */
    protected function post(string $uri, array $data = [], array $headers = []): Response
    {
        return $this->call('POST', $uri, $data, $headers);
    }

    /**
     * Visit the given URI with a PUT request
     */
    protected function put(string $uri, array $data = [], array $headers = []): Response
    {
        return $this->call('PUT', $uri, $data, $headers);
    }

    /**
     * Visit the given URI with a PATCH request
     */
    protected function patch(string $uri, array $data = [], array $headers = []): Response
    {
        return $this->call('PATCH', $uri, $data, $headers);
    }

    /**
     * Visit the given URI with a DELETE request
     */
    protected function delete(string $uri, array $data = [], array $headers = []): Response
    {
        return $this->call('DELETE', $uri, $data, $headers);
    }

    /**
     * Call the given URI
     */
    protected function call(string $method, string $uri, array $data = [], array $headers = []): Response
    {
        // Set up request environment
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $uri;
        
        if ($method === 'GET') {
            $_GET = $data;
        } else {
            $_POST = $data;
        }

        foreach ($headers as $key => $value) {
            $_SERVER['HTTP_' . strtoupper(str_replace('-', '_', $key))] = $value;
        }

        // Create request
        $request = new Request();

        // Handle request through router
        $router = app('router');
        
        try {
            $this->response = $router->dispatch($request);
        } catch (\Exception $e) {
            $this->response = new Response($e->getMessage(), 500);
        }

        return $this->response;
    }

    /**
     * Visit the given URI with a JSON request
     */
    protected function json(string $method, string $uri, array $data = [], array $headers = []): Response
    {
        $headers['Content-Type'] = 'application/json';
        $headers['Accept'] = 'application/json';

        $_SERVER['CONTENT_TYPE'] = 'application/json';

        return $this->call($method, $uri, $data, $headers);
    }

    /**
     * Assert that the response has a 200 status code
     */
    protected function assertOk(): void
    {
        $this->assertResponseStatus(200, $this->response);
    }

    /**
     * Assert that the response has a 404 status code
     */
    protected function assertNotFound(): void
    {
        $this->assertResponseStatus(404, $this->response);
    }

    /**
     * Assert that the response has a 403 status code
     */
    protected function assertForbidden(): void
    {
        $this->assertResponseStatus(403, $this->response);
    }

    /**
     * Assert that the response has a 401 status code
     */
    protected function assertUnauthorized(): void
    {
        $this->assertResponseStatus(401, $this->response);
    }

    /**
     * Assert that the response is a redirect to a given URI
     */
    protected function assertRedirectTo(string $uri): void
    {
        $this->assertRedirect($this->response, $uri);
    }

    /**
     * Assert response contains text
     */
    protected function assertSee(string $text): void
    {
        $content = $this->response->getContent();
        $this->assertStringContainsString($text, $content);
    }

    /**
     * Assert response does not contain text
     */
    protected function assertDontSee(string $text): void
    {
        $content = $this->response->getContent();
        $this->assertStringNotContainsString($text, $content);
    }
}
