# File Storage

## Introduction

NeoFramework provides a powerful filesystem abstraction that makes it incredibly simple to work with local filesystems, Amazon S3, and other cloud storage systems. The filesystem configuration file is located at `config/filesystems.php`.

## Configuration

The filesystem configuration is located at `config/filesystems.php`:

```php
return [
    'default' => env('FILESYSTEM_DRIVER', 'local'),

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
        ],

        'ftp' => [
            'driver' => 'ftp',
            'host' => env('FTP_HOST'),
            'username' => env('FTP_USERNAME'),
            'password' => env('FTP_PASSWORD'),
        ],
    ],
];
```

## Basic Usage

### Storing Files

```php
use NeoPhp\Support\Facades\Storage;

// Store file
Storage::put('file.txt', 'Contents');

// Store file with visibility
Storage::put('file.txt', 'Contents', 'public');

// Prepend to file
Storage::prepend('file.txt', 'Prepended Text');

// Append to file
Storage::append('file.txt', 'Appended Text');

// Copy file
Storage::copy('old/file.txt', 'new/file.txt');

// Move file
Storage::move('old/file.txt', 'new/file.txt');
```

### Retrieving Files

```php
// Get file contents
$contents = Storage::get('file.txt');

// Check if file exists
if (Storage::exists('file.txt')) {
    //
}

// Check if file is missing
if (Storage::missing('file.txt')) {
    //
}

// Get file size
$size = Storage::size('file.txt');

// Get last modified time
$time = Storage::lastModified('file.txt');
```

### Deleting Files

```php
// Delete single file
Storage::delete('file.txt');

// Delete multiple files
Storage::delete(['file1.txt', 'file2.txt']);

// Delete directory
Storage::deleteDirectory('directory');
```

### Directories

```php
// Get all files in directory
$files = Storage::files('directory');

// Get all files recursively
$files = Storage::allFiles('directory');

// Get all directories
$directories = Storage::directories('directory');

// Get all directories recursively
$directories = Storage::allDirectories('directory');

// Create directory
Storage::makeDirectory('directory');
```

## File Uploads

### Storing Uploaded Files

```php
public function store(Request $request)
{
    // Store in default disk
    $path = $request->file('avatar')->store('avatars');

    // Store in specific disk
    $path = $request->file('avatar')->store('avatars', 's3');

    // Store with custom name
    $path = $request->file('avatar')->storeAs(
        'avatars',
        $request->user()->id . '.jpg'
    );

    // Get file info
    $file = $request->file('avatar');
    $name = $file->getClientOriginalName();
    $extension = $file->getClientOriginalExtension();
    $size = $file->getSize();
}
```

## File Visibility

### Setting Visibility

```php
// Set file visibility
Storage::setVisibility('file.txt', 'public');

// Get file visibility
$visibility = Storage::getVisibility('file.txt');

// Store with visibility
Storage::put('file.txt', 'Contents', 'public');
```

## File URLs

### Generating URLs

```php
// Get URL for public disk
$url = Storage::url('file.jpg');

// Get temporary URL (S3)
$url = Storage::temporaryUrl(
    'file.jpg',
    now()->addMinutes(5)
);

// Get full path
$path = Storage::path('file.txt');
```

## Using Different Disks

```php
// Use specific disk
Storage::disk('s3')->put('file.txt', 'Contents');

// Get from specific disk
$contents = Storage::disk('s3')->get('file.txt');

// Check existence on specific disk
if (Storage::disk('s3')->exists('file.txt')) {
    //
}
```

## Custom Filesystems

### Creating Custom Disk

In `config/filesystems.php`:

```php
'disks' => [
    'custom' => [
        'driver' => 'local',
        'root' => storage_path('custom'),
    ],
],
```

## Practical Examples

### Example 1: Avatar Upload System

```php
<?php

namespace App\Services;

use NeoPhp\Support\Facades\Storage;
use NeoPhp\Support\Str;
use Intervention\Image\Facades\Image;

class AvatarService
{
    public function upload($file, $userId)
    {
        // Validate file
        if (!$file->isValid()) {
            throw new \Exception('Invalid file upload');
        }

        // Generate unique filename
        $filename = $userId . '_' . time() . '.' . $file->getClientOriginalExtension();

        // Create thumbnails
        $this->createThumbnails($file, $filename);

        // Store original
        $path = $file->storeAs('avatars/original', $filename, 'public');

        // Delete old avatar
        $this->deleteOldAvatar($userId);

        return [
            'original' => Storage::url('avatars/original/' . $filename),
            'large' => Storage::url('avatars/large/' . $filename),
            'medium' => Storage::url('avatars/medium/' . $filename),
            'small' => Storage::url('avatars/small/' . $filename),
        ];
    }

    protected function createThumbnails($file, $filename)
    {
        $sizes = [
            'large' => 500,
            'medium' => 300,
            'small' => 100,
        ];

        foreach ($sizes as $size => $dimension) {
            $image = Image::make($file)
                ->fit($dimension, $dimension)
                ->encode('jpg', 80);

            Storage::disk('public')->put(
                "avatars/{$size}/{$filename}",
                $image
            );
        }
    }

    protected function deleteOldAvatar($userId)
    {
        $sizes = ['original', 'large', 'medium', 'small'];

        foreach ($sizes as $size) {
            $files = Storage::disk('public')->files("avatars/{$size}");

            foreach ($files as $file) {
                if (Str::startsWith(basename($file), $userId . '_')) {
                    Storage::disk('public')->delete($file);
                }
            }
        }
    }

    public function delete($userId)
    {
        $this->deleteOldAvatar($userId);
    }
}

// Usage in controller
class ProfileController extends Controller
{
    protected $avatarService;

    public function __construct(AvatarService $avatarService)
    {
        $this->avatarService = $avatarService;
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|max:2048',
        ]);

        $urls = $this->avatarService->upload(
            $request->file('avatar'),
            auth()->id()
        );

        auth()->user()->update([
            'avatar' => $urls['medium'],
        ]);

        return back()->with('success', 'Avatar updated successfully');
    }
}
```

### Example 2: Document Management System

```php
<?php

namespace App\Services;

use App\Models\Document;
use NeoPhp\Support\Facades\Storage;
use NeoPhp\Support\Str;

class DocumentService
{
    public function upload($file, $category, $userId)
    {
        // Generate unique filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = "documents/{$category}/" . date('Y/m');

        // Store file
        $fullPath = $file->storeAs($path, $filename, 's3');

        // Create document record
        $document = Document::create([
            'user_id' => $userId,
            'category' => $category,
            'filename' => $file->getClientOriginalName(),
            'stored_filename' => $filename,
            'path' => $fullPath,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'disk' => 's3',
        ]);

        return $document;
    }

    public function download($documentId)
    {
        $document = Document::findOrFail($documentId);

        if (!Storage::disk($document->disk)->exists($document->path)) {
            throw new \Exception('File not found');
        }

        return Storage::disk($document->disk)->download(
            $document->path,
            $document->filename
        );
    }

    public function generateShareLink($documentId, $expiresInMinutes = 60)
    {
        $document = Document::findOrFail($documentId);

        if ($document->disk === 's3') {
            return Storage::disk('s3')->temporaryUrl(
                $document->path,
                now()->addMinutes($expiresInMinutes)
            );
        }

        // Generate signed URL for local storage
        return URL::temporarySignedRoute(
            'documents.download',
            now()->addMinutes($expiresInMinutes),
            ['document' => $documentId]
        );
    }

    public function delete($documentId)
    {
        $document = Document::findOrFail($documentId);

        // Delete file
        Storage::disk($document->disk)->delete($document->path);

        // Delete record
        $document->delete();
    }

    public function getStorageStats($userId)
    {
        $documents = Document::where('user_id', $userId)->get();

        return [
            'total_files' => $documents->count(),
            'total_size_mb' => round($documents->sum('size') / 1024 / 1024, 2),
            'by_category' => $documents->groupBy('category')->map(function ($docs) {
                return [
                    'count' => $docs->count(),
                    'size_mb' => round($docs->sum('size') / 1024 / 1024, 2),
                ];
            }),
        ];
    }
}

// Controller
class DocumentController extends Controller
{
    protected $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'category' => 'required|string',
        ]);

        $document = $this->documentService->upload(
            $request->file('file'),
            $request->category,
            auth()->id()
        );

        return response()->json([
            'message' => 'Document uploaded successfully',
            'document' => $document,
        ]);
    }

    public function download($id)
    {
        return $this->documentService->download($id);
    }

    public function share($id)
    {
        $url = $this->documentService->generateShareLink($id, 60);

        return response()->json(['url' => $url]);
    }

    public function stats()
    {
        $stats = $this->documentService->getStorageStats(auth()->id());

        return view('documents.stats', compact('stats'));
    }
}
```

### Example 3: Backup System

```php
<?php

namespace App\Services;

use NeoPhp\Support\Facades\Storage;
use NeoPhp\Support\Facades\DB;
use ZipArchive;

class BackupService
{
    public function createBackup()
    {
        $timestamp = now()->format('Y-m-d_His');
        $backupName = "backup_{$timestamp}";

        // Create temporary directory
        $tempDir = storage_path("temp/{$backupName}");
        mkdir($tempDir, 0755, true);

        // Backup database
        $this->backupDatabase($tempDir);

        // Backup files
        $this->backupFiles($tempDir);

        // Create zip archive
        $zipPath = $this->createZip($tempDir, $backupName);

        // Upload to S3
        $s3Path = $this->uploadToS3($zipPath, $backupName);

        // Cleanup
        $this->cleanup($tempDir, $zipPath);

        return [
            'name' => $backupName,
            'path' => $s3Path,
            'size' => Storage::disk('s3')->size($s3Path),
            'created_at' => now(),
        ];
    }

    protected function backupDatabase($dir)
    {
        $filename = "{$dir}/database.sql";

        // MySQL dump
        $command = sprintf(
            'mysqldump -u%s -p%s %s > %s',
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            config('database.connections.mysql.database'),
            $filename
        );

        exec($command);
    }

    protected function backupFiles($dir)
    {
        // Copy important directories
        $directories = [
            storage_path('app/public'),
            storage_path('uploads'),
        ];

        foreach ($directories as $source) {
            if (is_dir($source)) {
                $destination = $dir . '/' . basename($source);
                $this->copyDirectory($source, $destination);
            }
        }
    }

    protected function createZip($source, $name)
    {
        $zipPath = storage_path("temp/{$name}.zip");
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($source),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($source) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }

            $zip->close();
        }

        return $zipPath;
    }

    protected function uploadToS3($zipPath, $name)
    {
        $path = "backups/{$name}.zip";

        Storage::disk('s3')->put(
            $path,
            file_get_contents($zipPath)
        );

        return $path;
    }

    protected function cleanup($tempDir, $zipPath)
    {
        // Remove temporary directory
        $this->deleteDirectory($tempDir);

        // Remove zip file
        unlink($zipPath);
    }

    protected function copyDirectory($source, $destination)
    {
        mkdir($destination, 0755, true);

        $files = scandir($source);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $srcPath = "{$source}/{$file}";
                $dstPath = "{$destination}/{$file}";

                if (is_dir($srcPath)) {
                    $this->copyDirectory($srcPath, $dstPath);
                } else {
                    copy($srcPath, $dstPath);
                }
            }
        }
    }

    protected function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $path = "{$dir}/{$file}";
                if (is_dir($path)) {
                    $this->deleteDirectory($path);
                } else {
                    unlink($path);
                }
            }
        }

        rmdir($dir);
    }

    public function listBackups()
    {
        $files = Storage::disk('s3')->files('backups');

        return collect($files)->map(function ($file) {
            return [
                'name' => basename($file),
                'size' => Storage::disk('s3')->size($file),
                'modified' => Storage::disk('s3')->lastModified($file),
            ];
        })->sortByDesc('modified')->values();
    }

    public function restore($backupName)
    {
        // Download from S3
        $zipPath = storage_path("temp/{$backupName}");
        $contents = Storage::disk('s3')->get("backups/{$backupName}");
        file_put_contents($zipPath, $contents);

        // Extract
        $extractDir = storage_path("temp/restore_{$backupName}");
        $zip = new ZipArchive();
        $zip->open($zipPath);
        $zip->extractTo($extractDir);
        $zip->close();

        // Restore database
        $this->restoreDatabase($extractDir);

        // Restore files
        $this->restoreFiles($extractDir);

        // Cleanup
        $this->cleanup($extractDir, $zipPath);
    }

    protected function restoreDatabase($dir)
    {
        $sqlFile = "{$dir}/database.sql";

        if (file_exists($sqlFile)) {
            $command = sprintf(
                'mysql -u%s -p%s %s < %s',
                config('database.connections.mysql.username'),
                config('database.connections.mysql.password'),
                config('database.connections.mysql.database'),
                $sqlFile
            );

            exec($command);
        }
    }

    protected function restoreFiles($dir)
    {
        $restoreDirs = ['public', 'uploads'];

        foreach ($restoreDirs as $dirName) {
            $source = "{$dir}/{$dirName}";
            if (is_dir($source)) {
                $destination = storage_path("app/{$dirName}");
                $this->copyDirectory($source, $destination);
            }
        }
    }
}
```

## Best Practices

### 1. Use Appropriate Disk

```php
// Public files (images, downloads)
Storage::disk('public')->put('file.jpg', $contents);

// Private files (documents, invoices)
Storage::disk('local')->put('invoice.pdf', $contents);

// Cloud storage (backups, archives)
Storage::disk('s3')->put('backup.zip', $contents);
```

### 2. Validate Uploads

```php
$request->validate([
    'file' => 'required|file|mimes:pdf,doc,docx|max:2048',
    'image' => 'required|image|mimes:jpg,png|max:1024',
]);
```

### 3. Generate Unique Filenames

```php
$filename = Str::uuid() . '.' . $file->extension();
$filename = time() . '_' . $file->getClientOriginalName();
```

### 4. Handle Errors Gracefully

```php
try {
    Storage::put('file.txt', $contents);
} catch (\Exception $e) {
    Log::error('Failed to store file', [
        'error' => $e->getMessage(),
    ]);
    return back()->with('error', 'Upload failed');
}
```

### 5. Use Temporary URLs for Security

```php
// S3 temporary URL
$url = Storage::disk('s3')->temporaryUrl('file.pdf', now()->addMinutes(5));

// Signed route for local files
$url = URL::temporarySignedRoute('download', now()->addMinutes(5), ['file' => $id]);
```

## Next Steps

- [Queue](queue.md) - Queue file processing
- [Cache](cache.md) - Cache file metadata
- [Security](../security/authentication.md) - Secure file access
- [API Resources](../api/resources.md) - File upload APIs
