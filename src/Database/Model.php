<?php

namespace NeoPhp\Database;

use NeoPhp\Database\Concerns\HasRelationships;
use NeoPhp\Database\Concerns\QueriesRelationships;
use NeoPhp\Database\Concerns\HasEvents;
use NeoPhp\Database\Concerns\HasGlobalScopes;

abstract class Model
{
    use HasRelationships;
    use QueriesRelationships;
    use HasEvents;
    use HasGlobalScopes;

    protected static $table;
    protected static $primaryKey = 'id';
    protected static $timestamps = true;
    protected static $connection;
    protected static $booted = [];

    protected $attributes = [];
    protected $original = [];
    protected $exists = false;
    protected $relations = [];

    public function __construct(array $attributes = [])
    {
        $this->bootIfNotBooted();
        $this->fill($attributes);
    }

    /**
     * Boot the model if not already booted
     */
    protected function bootIfNotBooted(): void
    {
        $class = static::class;

        if (!isset(static::$booted[$class])) {
            static::$booted[$class] = true;
            static::boot();
        }
    }

    /**
     * Boot the model and traits
     */
    protected static function boot(): void
    {
        static::bootTraits();
    }

    /**
     * Boot all of the bootable traits on the model
     */
    protected static function bootTraits(): void
    {
        $class = static::class;

        foreach (class_uses_recursive($class) as $trait) {
            $method = 'boot' . class_basename($trait);

            if (method_exists($class, $method)) {
                forward_static_call([$class, $method]);
            }
        }
    }

    public static function setConnection(Database $db): void
    {
        static::$connection = $db;
    }

    protected static function getConnection(): Database
    {
        if (!static::$connection) {
            static::$connection = app('db');
        }

        return static::$connection;
    }

    public static function all(): array
    {
        return static::query()->get();
    }

    public static function find($id): ?self
    {
        $result = static::getConnection()->find(static::$table, $id, static::$primaryKey);
        
        if (!$result) {
            return null;
        }

        $model = new static($result);
        $model->exists = true;
        $model->original = $result;
        
        // Fire retrieved event
        $model->fireModelEvent('retrieved', false);
        
        return $model;
    }

    public static function where(string $column, $operator, $value = null): QueryBuilder
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $instance = new static();
        $builder = new QueryBuilder(static::getConnection(), static::$table, static::class);
        
        // Apply global scopes
        $instance->applyGlobalScopes($builder);
        
        return $builder->where($column, $operator, $value);
    }

    public static function query(): QueryBuilder
    {
        $instance = new static();
        $builder = new QueryBuilder(static::getConnection(), static::$table, static::class);
        
        // Apply global scopes
        $instance->applyGlobalScopes($builder);
        
        return $builder;
    }

    public static function create(array $attributes): self
    {
        $model = new static($attributes);
        $model->save();
        
        return $model;
    }

    public function save(): bool
    {
        // Fire saving event
        if ($this->fireModelEvent('saving') === false) {
            return false;
        }

        if (static::$timestamps) {
            $now = date('Y-m-d H:i:s');
            
            if (!$this->exists) {
                $this->attributes['created_at'] = $now;
            }
            
            $this->attributes['updated_at'] = $now;
        }

        if ($this->exists) {
            return $this->performUpdate();
        }

        return $this->performInsert();
    }

    protected function performInsert(): bool
    {
        // Fire creating event
        if ($this->fireModelEvent('creating') === false) {
            return false;
        }

        $id = static::getConnection()->insert(static::$table, $this->attributes);
        
        $this->setAttribute(static::$primaryKey, $id);
        $this->exists = true;
        $this->original = $this->attributes;

        // Fire created and saved events
        $this->fireModelEvent('created', false);
        $this->fireModelEvent('saved', false);
        
        return true;
    }

    protected function performUpdate(): bool
    {
        // Fire updating event
        if ($this->fireModelEvent('updating') === false) {
            return false;
        }

        $id = $this->attributes[static::$primaryKey];
        $where = static::$primaryKey . ' = ?';
        
        $updated = static::getConnection()->update(
            static::$table,
            $this->attributes,
            $where,
            [$id]
        );

        $this->original = $this->attributes;

        // Fire updated and saved events
        $this->fireModelEvent('updated', false);
        $this->fireModelEvent('saved', false);
        
        return $updated > 0;
    }

    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        // Fire deleting event
        if ($this->fireModelEvent('deleting') === false) {
            return false;
        }

        $id = $this->attributes[static::$primaryKey];
        $where = static::$primaryKey . ' = ?';
        
        $deleted = static::getConnection()->delete(static::$table, $where, [$id]);
        
        $this->exists = false;

        // Fire deleted event
        $this->fireModelEvent('deleted', false);
        
        return $deleted > 0;
    }

    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    public function setAttribute(string $key, $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    public function getAttribute(string $key, $default = null)
    {
        return $this->attributes[$key] ?? $default;
    }

    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function toJson(): string
    {
        return json_encode($this->attributes);
    }

    public function fresh(): ?self
    {
        if (!$this->exists) {
            return null;
        }

        return static::find($this->attributes[static::$primaryKey]);
    }

    public static function paginate(int $perPage = 15, int $page = null): \NeoPhp\Pagination\Paginator
    {
        $page = $page ?? (int) ($_GET['page'] ?? 1);
        $offset = ($page - 1) * $perPage;
        
        $db = static::getConnection();
        
        // Get total count
        $totalResult = $db->query("SELECT COUNT(*) as total FROM " . static::$table);
        $total = (int) $totalResult[0]['total'];
        
        // Get items
        $results = $db->query(
            "SELECT * FROM " . static::$table . " LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        
        $items = array_map(function ($item) {
            return new static($item);
        }, $results);
        
        return new \NeoPhp\Pagination\Paginator($items, $total, $perPage, $page);
    }
}
