<?php

namespace NeoPhp\Http;

/**
 * Uploaded File
 * 
 * Handles uploaded files from requests
 */
class UploadedFile
{
    protected array $file;

    public function __construct(array $file)
    {
        $this->file = $file;
    }

    /**
     * Get the original name of the file
     */
    public function getClientOriginalName(): string
    {
        return $this->file['name'] ?? '';
    }

    /**
     * Get the file extension
     */
    public function getClientOriginalExtension(): string
    {
        return pathinfo($this->getClientOriginalName(), PATHINFO_EXTENSION);
    }

    /**
     * Get the file size in bytes
     */
    public function getSize(): int
    {
        return $this->file['size'] ?? 0;
    }

    /**
     * Get the mime type
     */
    public function getMimeType(): string
    {
        return $this->file['type'] ?? '';
    }

    /**
     * Get the temporary file path
     */
    public function getPathname(): string
    {
        return $this->file['tmp_name'] ?? '';
    }

    /**
     * Get the file error code
     */
    public function getError(): int
    {
        return $this->file['error'] ?? UPLOAD_ERR_OK;
    }

    /**
     * Check if the file uploaded successfully
     */
    public function isValid(): bool
    {
        return $this->getError() === UPLOAD_ERR_OK;
    }

    /**
     * Store the file
     */
    public function store(string $path, string $disk = 'local'): string
    {
        $filename = $this->hashName();
        return $this->storeAs($path, $filename, $disk);
    }

    /**
     * Store the file with a given name
     */
    public function storeAs(string $path, string $name, string $disk = 'local'): string
    {
        $storage = app('storage');
        $fullPath = rtrim($path, '/') . '/' . $name;

        $content = file_get_contents($this->getPathname());
        $storage->put($fullPath, $content);

        return $fullPath;
    }

    /**
     * Move the file to a new location
     */
    public function move(string $directory, string $name = null): string
    {
        $name = $name ?? $this->hashName();
        $path = $directory . '/' . $name;

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        move_uploaded_file($this->getPathname(), $path);

        return $path;
    }

    /**
     * Generate a hashed name for the file
     */
    public function hashName(string $path = null): string
    {
        $hash = bin2hex(random_bytes(16));
        $extension = $this->getClientOriginalExtension();

        $name = $hash . '.' . $extension;

        return $path ? $path . '/' . $name : $name;
    }

    /**
     * Get a filename with the original name
     */
    public function clientName(string $path = null): string
    {
        $name = $this->getClientOriginalName();
        return $path ? $path . '/' . $name : $name;
    }

    /**
     * Check if file is an image
     */
    public function isImage(): bool
    {
        $mimeType = $this->getMimeType();
        return str_starts_with($mimeType, 'image/');
    }

    /**
     * Get image dimensions
     */
    public function dimensions(): ?array
    {
        if (!$this->isImage()) {
            return null;
        }

        $size = getimagesize($this->getPathname());

        if ($size === false) {
            return null;
        }

        return [
            'width' => $size[0],
            'height' => $size[1]
        ];
    }

    /**
     * Get file contents
     */
    public function getContent(): string
    {
        return file_get_contents($this->getPathname());
    }

    /**
     * Get file as resource
     */
    public function getStream()
    {
        return fopen($this->getPathname(), 'r');
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->getClientOriginalName(),
            'size' => $this->getSize(),
            'mime' => $this->getMimeType(),
            'extension' => $this->getClientOriginalExtension(),
            'is_valid' => $this->isValid(),
            'is_image' => $this->isImage(),
        ];
    }
}
