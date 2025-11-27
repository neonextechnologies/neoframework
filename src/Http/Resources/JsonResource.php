<?php

namespace NeoPhp\Http\Resources;

use NeoPhp\Http\Response;

/**
 * JSON Resource
 * 
 * Transform models into JSON responses
 */
abstract class JsonResource
{
    protected $resource;
    protected array $with = [];
    protected array $additional = [];

    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    /**
     * Transform the resource into an array
     */
    abstract public function toArray($request): array;

    /**
     * Create a new resource collection
     */
    public static function collection($resource): ResourceCollection
    {
        return new ResourceCollection($resource, static::class);
    }

    /**
     * Convert the resource to a response
     */
    public function toResponse($request = null): Response
    {
        return response()->json($this->resolve($request));
    }

    /**
     * Resolve the resource to an array
     */
    public function resolve($request = null): array
    {
        $data = $this->toArray($request);

        if (!empty($this->with)) {
            $data = array_merge($data, $this->with);
        }

        if (!empty($this->additional)) {
            $data = array_merge($data, $this->additional);
        }

        return $data;
    }

    /**
     * Add additional data to the resource
     */
    public function additional(array $data): self
    {
        $this->additional = array_merge($this->additional, $data);
        return $this;
    }

    /**
     * Get additional data that should be returned with the resource array
     */
    public function with($request): array
    {
        return [];
    }

    /**
     * Conditionally include attributes
     */
    protected function when(bool $condition, $value, $default = null)
    {
        if ($condition) {
            return $value instanceof \Closure ? $value() : $value;
        }

        return $default;
    }

    /**
     * Merge a value based on a condition
     */
    protected function mergeWhen(bool $condition, $value): array
    {
        return $condition ? (is_array($value) ? $value : [$value]) : [];
    }

    /**
     * Include attributes when loaded
     */
    protected function whenLoaded(string $relationship, $value = null, $default = null)
    {
        if (!$this->resource) {
            return $default;
        }

        // Check if relationship is loaded
        $loaded = false;
        
        if (is_object($this->resource) && method_exists($this->resource, 'relationLoaded')) {
            $loaded = $this->resource->relationLoaded($relationship);
        } elseif (is_object($this->resource) && isset($this->resource->relations[$relationship])) {
            $loaded = true;
        }

        if (!$loaded) {
            return $default;
        }

        if (is_null($value)) {
            $relationData = is_object($this->resource) 
                ? ($this->resource->$relationship ?? null)
                : null;
            return $relationData;
        }

        return $value instanceof \Closure ? $value() : $value;
    }

    /**
     * Include pivotattributes
     */
    protected function whenPivotLoaded(string $table, $value, $default = null)
    {
        if (!$this->resource || !isset($this->resource->pivot)) {
            return $default;
        }

        return $value instanceof \Closure ? $value() : $value;
    }

    /**
     * Get the underlying resource
     */
    public function resource()
    {
        return $this->resource;
    }

    /**
     * Magic method to access resource properties
     */
    public function __get(string $key)
    {
        return $this->resource->$key ?? null;
    }

    /**
     * Magic method to check if property exists
     */
    public function __isset(string $key): bool
    {
        return isset($this->resource->$key);
    }

    /**
     * Magic method to call resource methods
     */
    public function __call(string $method, array $parameters)
    {
        if (method_exists($this->resource, $method)) {
            return $this->resource->$method(...$parameters);
        }

        throw new \BadMethodCallException("Method {$method} does not exist.");
    }
}
