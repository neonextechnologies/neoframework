<?php

use NeoPhp\Testing\TestCase;

class ExampleUnitTest extends TestCase
{
    /**
     * Test basic assertion.
     */
    public function test_basic_assertion(): void
    {
        $this->assertTrue(true);
        $this->assertFalse(false);
        $this->assertEquals(1, 1);
    }

    /**
     * Test array operations.
     */
    public function test_array_has_key(): void
    {
        $array = ['name' => 'John', 'age' => 30];
        
        $this->assertArrayHasKey('name', $array);
        $this->assertEquals('John', $array['name']);
    }

    /**
     * Test string operations.
     */
    public function test_string_contains(): void
    {
        $string = 'NeoFramework is awesome';
        
        $this->assertStringContainsString('NeoFramework', $string);
        $this->assertStringContainsString('awesome', $string);
    }

    /**
     * Test custom helper function.
     */
    public function test_helper_function(): void
    {
        $this->assertEquals('production', config('app.env'));
        $this->assertEquals('NeoPhp Application', config('app.name'));
    }
}
