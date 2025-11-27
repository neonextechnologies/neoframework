<?php

namespace NeoPhp\Debug;

use NeoPhp\Http\Request;
use NeoPhp\Http\Response;

class DebugBar
{
    protected array $collectors = [];
    protected bool $enabled = true;
    protected float $startTime;
    protected int $startMemory;

    public function __construct()
    {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage();
        
        $this->registerDefaultCollectors();
    }

    /**
     * Register default data collectors.
     */
    protected function registerDefaultCollectors(): void
    {
        $this->addCollector('time', function () {
            return [
                'start' => $this->startTime,
                'end' => microtime(true),
                'duration' => round((microtime(true) - $this->startTime) * 1000, 2),
            ];
        });

        $this->addCollector('memory', function () {
            return [
                'start' => $this->startMemory,
                'end' => memory_get_usage(),
                'peak' => memory_get_peak_usage(),
                'usage' => round((memory_get_usage() - $this->startMemory) / 1024 / 1024, 2),
            ];
        });

        $this->addCollector('request', function () {
            $request = app(\NeoPhp\Http\Request::class);
            return [
                'method' => $request->getMethod(),
                'uri' => $request->getUri(),
                'headers' => $request->headers(),
                'query' => $request->query(),
                'post' => $request->post(),
            ];
        });
    }

    /**
     * Add a data collector.
     */
    public function addCollector(string $name, callable $collector): void
    {
        $this->collectors[$name] = $collector;
    }

    /**
     * Enable the debug bar.
     */
    public function enable(): void
    {
        $this->enabled = true;
    }

    /**
     * Disable the debug bar.
     */
    public function disable(): void
    {
        $this->enabled = false;
    }

    /**
     * Check if debug bar is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Collect all data.
     */
    public function collect(): array
    {
        $data = [];
        
        foreach ($this->collectors as $name => $collector) {
            try {
                $data[$name] = $collector();
            } catch (\Throwable $e) {
                $data[$name] = ['error' => $e->getMessage()];
            }
        }

        return $data;
    }

    /**
     * Inject debug bar into response.
     */
    public function injectDebugBar(Response $response): Response
    {
        if (!$this->enabled) {
            return $response;
        }

        $content = $response->getContent();
        
        // Only inject into HTML responses
        if (!str_contains($response->headers()['Content-Type'] ?? 'text/html', 'text/html')) {
            return $response;
        }

        $debugBarHtml = $this->renderDebugBar();
        
        // Inject before closing body tag
        if (str_contains($content, '</body>')) {
            $content = str_replace('</body>', $debugBarHtml . '</body>', $content);
            $response->setContent($content);
        }

        return $response;
    }

    /**
     * Render the debug bar HTML.
     */
    protected function renderDebugBar(): string
    {
        $data = $this->collect();
        $dataJson = json_encode($data, JSON_PRETTY_PRINT);

        return <<<HTML
<div id="neo-debug-bar" style="position: fixed; bottom: 0; left: 0; right: 0; background: #1a1a1a; color: #e0e0e0; font-family: monospace; font-size: 12px; z-index: 999999; box-shadow: 0 -2px 10px rgba(0,0,0,0.3);">
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 15px; border-bottom: 1px solid #333;">
        <div style="display: flex; gap: 20px;">
            <div class="neo-debug-item" onclick="neoDebugToggle('request')">
                <strong>⚡</strong> {$data['request']['method']} {$data['request']['uri']}
            </div>
            <div class="neo-debug-item" onclick="neoDebugToggle('time')">
                <strong>⏱️</strong> {$data['time']['duration']}ms
            </div>
            <div class="neo-debug-item" onclick="neoDebugToggle('memory')">
                <strong>💾</strong> {$data['memory']['usage']}MB
            </div>
            <div class="neo-debug-item" onclick="neoDebugToggle('queries')" style="cursor: pointer;">
                <strong>🗄️</strong> Queries
            </div>
            <div class="neo-debug-item" onclick="neoDebugToggle('routes')" style="cursor: pointer;">
                <strong>🛣️</strong> Routes
            </div>
            <div class="neo-debug-item" onclick="neoDebugToggle('logs')" style="cursor: pointer;">
                <strong>📝</strong> Logs
            </div>
        </div>
        <div style="cursor: pointer;" onclick="neoDebugClose()">✕</div>
    </div>
    <div id="neo-debug-content" style="display: none; padding: 15px; max-height: 400px; overflow-y: auto; background: #2c2c2c;">
        <pre style="margin: 0; white-space: pre-wrap;">{$dataJson}</pre>
    </div>
</div>
<style>
    .neo-debug-item { cursor: pointer; padding: 5px 10px; border-radius: 4px; transition: background 0.2s; }
    .neo-debug-item:hover { background: #333; }
</style>
<script>
    let neoDebugOpen = false;
    function neoDebugToggle(section) {
        const content = document.getElementById('neo-debug-content');
        if (neoDebugOpen) {
            content.style.display = 'none';
            neoDebugOpen = false;
        } else {
            content.style.display = 'block';
            neoDebugOpen = true;
        }
    }
    function neoDebugClose() {
        document.getElementById('neo-debug-bar').style.display = 'none';
    }
</script>
HTML;
    }

    /**
     * Log a query to the debug bar.
     */
    public static function logQuery(string $sql, array $bindings = [], float $time = 0): void
    {
        static $queries = [];
        
        $queries[] = [
            'sql' => $sql,
            'bindings' => $bindings,
            'time' => $time,
        ];

        // Add to collector if not already added
        $debugBar = app(self::class);
        $debugBar->addCollector('queries', function () use ($queries) {
            return [
                'count' => count($queries),
                'total_time' => array_sum(array_column($queries, 'time')),
                'queries' => $queries,
            ];
        });
    }

    /**
     * Log a message to the debug bar.
     */
    public static function log(string $level, string $message, array $context = []): void
    {
        static $logs = [];
        
        $logs[] = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'time' => microtime(true),
        ];

        $debugBar = app(self::class);
        $debugBar->addCollector('logs', function () use ($logs) {
            return [
                'count' => count($logs),
                'logs' => $logs,
            ];
        });
    }
}
