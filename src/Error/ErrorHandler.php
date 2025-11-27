<?php

namespace NeoPhp\Error;

use Throwable;
use NeoPhp\Http\Response;
use NeoPhp\Http\Request;

class ErrorHandler
{
    protected bool $debug;
    protected array $dontReport = [];

    public function __construct(bool $debug = false)
    {
        $this->debug = $debug;
    }

    /**
     * Register the error and exception handlers.
     */
    public function register(): void
    {
        error_reporting(E_ALL);
        
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * Handle PHP errors.
     */
    public function handleError(int $level, string $message, string $file = '', int $line = 0): bool
    {
        if (error_reporting() & $level) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }

        return false;
    }

    /**
     * Handle uncaught exceptions.
     */
    public function handleException(Throwable $e): void
    {
        if (!$this->shouldReport($e)) {
            return;
        }

        $this->report($e);
        $this->render($e)->send();
    }

    /**
     * Handle fatal errors.
     */
    public function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            $this->handleException(new \ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            ));
        }
    }

    /**
     * Report the exception.
     */
    protected function report(Throwable $e): void
    {
        // Log the exception
        if (function_exists('logger')) {
            logger()->error($e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Render the exception into an HTTP response.
     */
    protected function render(Throwable $e): Response
    {
        $statusCode = $this->getStatusCode($e);

        if ($this->debug) {
            return $this->renderDebugPage($e, $statusCode);
        }

        return $this->renderProductionPage($e, $statusCode);
    }

    /**
     * Render debug error page (development).
     */
    protected function renderDebugPage(Throwable $e, int $statusCode): Response
    {
        $content = $this->renderDebugHtml($e, $statusCode);
        return new Response($content, $statusCode, ['Content-Type' => 'text/html']);
    }

    /**
     * Render production error page.
     */
    protected function renderProductionPage(Throwable $e, int $statusCode): Response
    {
        $content = $this->renderProductionHtml($e, $statusCode);
        return new Response($content, $statusCode, ['Content-Type' => 'text/html']);
    }

    /**
     * Get the HTTP status code from exception.
     */
    protected function getStatusCode(Throwable $e): int
    {
        if (method_exists($e, 'getStatusCode')) {
            return $e->getStatusCode();
        }

        if ($e instanceof \InvalidArgumentException) {
            return 400;
        }

        return 500;
    }

    /**
     * Check if exception should be reported.
     */
    protected function shouldReport(Throwable $e): bool
    {
        foreach ($this->dontReport as $type) {
            if ($e instanceof $type) {
                return false;
            }
        }

        return true;
    }

    /**
     * Render debug HTML.
     */
    protected function renderDebugHtml(Throwable $e, int $statusCode): string
    {
        $exceptionClass = get_class($e);
        $message = $e->getMessage();
        $file = $e->getFile();
        $line = $e->getLine();
        $trace = $e->getTraceAsString();
        $code = $this->getFileContext($file, $line);

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error {$statusCode} - {$exceptionClass}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #1a1a1a; color: #e0e0e0; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { background: #c0392b; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .header h1 { font-size: 24px; margin-bottom: 10px; }
        .header p { font-size: 16px; opacity: 0.9; }
        .section { background: #2c2c2c; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .section h2 { font-size: 18px; margin-bottom: 15px; color: #3498db; }
        .file-info { background: #363636; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-family: monospace; }
        .code { background: #1e1e1e; padding: 15px; border-radius: 4px; overflow-x: auto; font-family: 'Consolas', monospace; font-size: 14px; line-height: 1.5; }
        .code-line { display: flex; }
        .line-number { color: #666; padding-right: 15px; user-select: none; min-width: 40px; text-align: right; }
        .line-content { flex: 1; }
        .line-error { background: #5a1f1f; }
        .trace { background: #1e1e1e; padding: 15px; border-radius: 4px; font-family: monospace; font-size: 13px; white-space: pre-wrap; }
        .highlight { color: #e74c3c; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{$exceptionClass}</h1>
            <p>{$message}</p>
        </div>
        
        <div class="section">
            <h2>Exception Location</h2>
            <div class="file-info">
                <strong>File:</strong> {$file}<br>
                <strong>Line:</strong> {$line}
            </div>
            <div class="code">
                {$code}
            </div>
        </div>
        
        <div class="section">
            <h2>Stack Trace</h2>
            <div class="trace">{$trace}</div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Render production HTML.
     */
    protected function renderProductionHtml(Throwable $e, int $statusCode): string
    {
        $title = $this->getStatusText($statusCode);
        $message = $this->getStatusMessage($statusCode);

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$statusCode} - {$title}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .container { text-align: center; color: white; }
        .error-code { font-size: 120px; font-weight: bold; text-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .error-title { font-size: 32px; margin: 20px 0; }
        .error-message { font-size: 18px; opacity: 0.9; margin-bottom: 30px; }
        .btn { display: inline-block; padding: 12px 30px; background: white; color: #667eea; text-decoration: none; border-radius: 25px; font-weight: 500; transition: transform 0.2s; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-code">{$statusCode}</div>
        <h1 class="error-title">{$title}</h1>
        <p class="error-message">{$message}</p>
        <a href="/" class="btn">Go Back Home</a>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Get file context around the error line.
     */
    protected function getFileContext(string $file, int $line, int $contextLines = 10): string
    {
        if (!file_exists($file)) {
            return '<div class="line-error">File not found</div>';
        }

        $lines = file($file);
        $start = max(0, $line - $contextLines - 1);
        $end = min(count($lines), $line + $contextLines);

        $html = '';
        for ($i = $start; $i < $end; $i++) {
            $lineNumber = $i + 1;
            $content = htmlspecialchars($lines[$i]);
            $class = $lineNumber === $line ? 'line-error' : '';
            
            $html .= '<div class="code-line ' . $class . '">';
            $html .= '<span class="line-number">' . $lineNumber . '</span>';
            $html .= '<span class="line-content">' . $content . '</span>';
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Get status text from code.
     */
    protected function getStatusText(int $code): string
    {
        $statuses = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            419 => 'Page Expired',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable',
        ];

        return $statuses[$code] ?? 'Error';
    }

    /**
     * Get status message from code.
     */
    protected function getStatusMessage(int $code): string
    {
        $messages = [
            400 => 'The request could not be understood by the server.',
            401 => 'You need to be authenticated to access this resource.',
            403 => 'You don\'t have permission to access this resource.',
            404 => 'The page you are looking for could not be found.',
            405 => 'The method is not allowed for this resource.',
            419 => 'Your session has expired. Please refresh and try again.',
            422 => 'The given data was invalid.',
            429 => 'Too many requests. Please slow down.',
            500 => 'Something went wrong on our end. We\'re working to fix it.',
            503 => 'The service is temporarily unavailable. Please try again later.',
        ];

        return $messages[$code] ?? 'An error occurred while processing your request.';
    }
}
