<?php

namespace NeoPhp\Testing;

use PHPUnit\Framework\TestCase as BaseTestCase;
use NeoPhp\Core\Application;

/**
 * Base Test Case
 * 
 * Base class for all tests
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use InteractsWithDatabase;
    use MakesHttpRequests;
    use InteractsWithAuthentication;

    protected ?Application $app = null;

    /**
     * Setup the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!$this->app) {
            $this->refreshApplication();
        }
    }

    /**
     * Clean up the testing environment
     */
    protected function tearDown(): void
    {
        if ($this->app) {
            $this->app = null;
        }

        parent::tearDown();
    }

    /**
     * Get the application instance
     */
    protected function app(): Application
    {
        if (!$this->app) {
            $this->refreshApplication();
        }

        return $this->app;
    }

    /**
     * Assert that the response has a successful status code
     */
    protected function assertResponseOk($response): void
    {
        $this->assertTrue(
            $response->getStatusCode() >= 200 && $response->getStatusCode() < 300,
            'Response status code is not successful'
        );
    }

    /**
     * Assert that the response has a specific status code
     */
    protected function assertResponseStatus(int $status, $response): void
    {
        $this->assertEquals($status, $response->getStatusCode());
    }

    /**
     * Assert that the response is a redirect
     */
    protected function assertRedirect($response, ?string $url = null): void
    {
        $this->assertTrue(
            $response->getStatusCode() >= 300 && $response->getStatusCode() < 400,
            'Response is not a redirect'
        );

        if ($url) {
            $this->assertEquals($url, $response->headers['Location'] ?? null);
        }
    }

    /**
     * Assert that the response contains JSON
     */
    protected function assertJson($response): void
    {
        $content = $response->getContent();
        json_decode($content);
        
        $this->assertEquals(JSON_ERROR_NONE, json_last_error(), 'Response is not valid JSON');
    }

    /**
     * Assert that the response JSON contains specific data
     */
    protected function assertJsonFragment(array $data, $response): void
    {
        $content = json_decode($response->getContent(), true);
        
        foreach ($data as $key => $value) {
            $this->assertArrayHasKey($key, $content);
            $this->assertEquals($value, $content[$key]);
        }
    }

    /**
     * Assert that a string contains another string
     */
    protected function assertStringContains(string $needle, string $haystack): void
    {
        $this->assertStringContainsString($needle, $haystack);
    }

    /**
     * Assert that an array has a specific structure
     */
    protected function assertArrayStructure(array $structure, array $array): void
    {
        foreach ($structure as $key) {
            $this->assertArrayHasKey($key, $array, "Array does not have key: {$key}");
        }
    }

    /**
     * Mock an object
     */
    protected function mock(string $class, ?\Closure $callback = null): object
    {
        $mock = $this->createMock($class);

        if ($callback) {
            $callback($mock);
        }

        $this->app->instance($class, $mock);

        return $mock;
    }

    /**
     * Spy on an object
     */
    protected function spy(string $class): object
    {
        $spy = $this->createMock($class);
        $this->app->instance($class, $spy);

        return $spy;
    }
}
