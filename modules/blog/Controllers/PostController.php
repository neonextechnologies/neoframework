<?php

namespace Modules\Blog\Controllers;

use App\Controllers\Controller;

/**
 * Post Controller
 * 
 * Handle blog post operations
 */
class PostController extends Controller
{
    /**
     * Display a listing of posts
     */
    public function index()
    {
        // Example: $posts = Post::latest()->paginate(10);
        
        return view('blog::index', [
            'title' => 'Blog Posts',
            'posts' => []
        ]);
    }

    /**
     * Display a single post
     */
    public function show(int $id)
    {
        // Example: $post = Post::findOrFail($id);
        
        return view('blog::show', [
            'post' => null
        ]);
    }

    /**
     * Admin: List all posts
     */
    public function adminIndex()
    {
        return view('blog::admin.index', [
            'posts' => []
        ]);
    }

    /**
     * Admin: Show create form
     */
    public function create()
    {
        return view('blog::admin.create');
    }

    /**
     * Admin: Store new post
     */
    public function store()
    {
        // Validate and store post
        // Example:
        // $validated = request()->validate([
        //     'title' => 'required|max:255',
        //     'content' => 'required',
        // ]);
        // 
        // $post = Post::create($validated);
        
        return redirect('/admin/blog/posts')->with('success', 'Post created successfully!');
    }

    /**
     * Admin: Show edit form
     */
    public function edit(int $id)
    {
        // Example: $post = Post::findOrFail($id);
        
        return view('blog::admin.edit', [
            'post' => null
        ]);
    }

    /**
     * Admin: Update post
     */
    public function update(int $id)
    {
        // Validate and update post
        
        return redirect('/admin/blog/posts')->with('success', 'Post updated successfully!');
    }

    /**
     * Admin: Delete post
     */
    public function destroy(int $id)
    {
        // Example: Post::findOrFail($id)->delete();
        
        return redirect('/admin/blog/posts')->with('success', 'Post deleted successfully!');
    }
}
