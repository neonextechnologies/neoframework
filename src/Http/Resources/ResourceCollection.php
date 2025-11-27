<?php

namespace NeoPhp\Http\Resources;

use NeoPhp\Http\Response;
use NeoPhp\Pagination\Paginator;

/**
 * Resource Collection
 * 
 * Transform collections of models into JSON responses
 */
class ResourceCollection
{
    protected $collection;
    protected string $resourceClass;
    protected array $additional = [];

    public function __construct($collection, string $resourceClass)
    {
        $this->collection = $collection;
        $this->resourceClass = $resourceClass;
    }

    /**
     * Transform the collection into an array
     */
    public function toArray($request = null): array
    {
        $data = [];

        foreach ($this->collection as $item) {
            $resource = new $this->resourceClass($item);
            $data[] = $resource->toArray($request);
        }

        return $data;
    }

    /**
     * Convert the collection to a response
     */
    public function toResponse($request = null): Response
    {
        $data = ['data' => $this->toArray($request)];

        // Add pagination meta if available
        if ($this->collection instanceof Paginator) {
            $data['meta'] = $this->paginationMeta();
            $data['links'] = $this->paginationLinks();
        }

        // Add additional data
        if (!empty($this->additional)) {
            $data = array_merge($data, $this->additional);
        }

        return response()->json($data);
    }

    /**
     * Add additional data to the collection
     */
    public function additional(array $data): self
    {
        $this->additional = array_merge($this->additional, $data);
        return $this;
    }

    /**
     * Get pagination metadata
     */
    protected function paginationMeta(): array
    {
        if (!$this->collection instanceof Paginator) {
            return [];
        }

        return [
            'current_page' => $this->collection->currentPage(),
            'from' => $this->collection->firstItem(),
            'last_page' => $this->collection->lastPage(),
            'per_page' => $this->collection->perPage(),
            'to' => $this->collection->lastItem(),
            'total' => $this->collection->total(),
        ];
    }

    /**
     * Get pagination links
     */
    protected function paginationLinks(): array
    {
        if (!$this->collection instanceof Paginator) {
            return [];
        }

        return [
            'first' => $this->collection->url(1),
            'last' => $this->collection->url($this->collection->lastPage()),
            'prev' => $this->collection->previousPageUrl(),
            'next' => $this->collection->nextPageUrl(),
        ];
    }

    /**
     * Get the underlying collection
     */
    public function collection()
    {
        return $this->collection;
    }

    /**
     * Count the items in the collection
     */
    public function count(): int
    {
        return count($this->collection);
    }

    /**
     * Check if collection is empty
     */
    public function isEmpty(): bool
    {
        return empty($this->collection);
    }

    /**
     * Check if collection is not empty
     */
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }
}
