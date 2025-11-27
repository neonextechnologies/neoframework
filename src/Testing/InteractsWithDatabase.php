<?php

namespace NeoPhp\Testing;

/**
 * Interacts With Database Trait
 * 
 * Provides database testing helpers
 */
trait InteractsWithDatabase
{
    /**
     * Assert that a record exists in the database
     */
    protected function assertDatabaseHas(string $table, array $data): void
    {
        $db = app('db');
        
        $query = "SELECT COUNT(*) as count FROM {$table} WHERE ";
        $bindings = [];
        $conditions = [];

        foreach ($data as $key => $value) {
            $conditions[] = "{$key} = ?";
            $bindings[] = $value;
        }

        $query .= implode(' AND ', $conditions);
        
        $result = $db->query($query, $bindings);
        $count = $result[0]['count'] ?? 0;

        $this->assertTrue($count > 0, "Failed asserting that table [{$table}] has matching record");
    }

    /**
     * Assert that a record does not exist in the database
     */
    protected function assertDatabaseMissing(string $table, array $data): void
    {
        $db = app('db');
        
        $query = "SELECT COUNT(*) as count FROM {$table} WHERE ";
        $bindings = [];
        $conditions = [];

        foreach ($data as $key => $value) {
            $conditions[] = "{$key} = ?";
            $bindings[] = $value;
        }

        $query .= implode(' AND ', $conditions);
        
        $result = $db->query($query, $bindings);
        $count = $result[0]['count'] ?? 0;

        $this->assertTrue($count === 0, "Failed asserting that table [{$table}] does not have matching record");
    }

    /**
     * Assert that a table has a specific number of records
     */
    protected function assertDatabaseCount(string $table, int $count): void
    {
        $db = app('db');
        
        $result = $db->query("SELECT COUNT(*) as count FROM {$table}");
        $actualCount = $result[0]['count'] ?? 0;

        $this->assertEquals($count, $actualCount, "Failed asserting that table [{$table}] has {$count} records");
    }

    /**
     * Begin a database transaction
     */
    protected function beginDatabaseTransaction(): void
    {
        $db = app('db');
        $db->query('START TRANSACTION');
    }

    /**
     * Rollback database transaction
     */
    protected function rollbackDatabaseTransaction(): void
    {
        $db = app('db');
        $db->query('ROLLBACK');
    }

    /**
     * Seed the database
     */
    protected function seed(string $seederClass = null): void
    {
        if ($seederClass) {
            $seeder = new $seederClass();
            $seeder->run();
        }
    }
}
