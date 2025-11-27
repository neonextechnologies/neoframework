<?php

/**
 * Blog Module Routes
 * 
 * Define blog module routes here
 */

use NeoPhp\Routing\Route;
use Modules\Blog\Controllers\PostController;

// Blog routes
Route::get('/blog', [PostController::class, 'index'])->name('blog.index');
Route::get('/blog/{id}', [PostController::class, 'show'])->name('blog.show');

// Admin routes (example)
Route::prefix('/admin/blog')->group(function() {
    Route::get('/posts', [PostController::class, 'adminIndex'])->name('blog.admin.index');
    Route::get('/posts/create', [PostController::class, 'create'])->name('blog.admin.create');
    Route::post('/posts', [PostController::class, 'store'])->name('blog.admin.store');
    Route::get('/posts/{id}/edit', [PostController::class, 'edit'])->name('blog.admin.edit');
    Route::put('/posts/{id}', [PostController::class, 'update'])->name('blog.admin.update');
    Route::delete('/posts/{id}', [PostController::class, 'destroy'])->name('blog.admin.destroy');
});
