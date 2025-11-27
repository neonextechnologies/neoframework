# Blog Module

A complete blog module for NeoFramework with posts, comments, and categories.

## Features

- ✅ Create, Read, Update, Delete posts
- ✅ Post categories and tags
- ✅ Comments system
- ✅ SEO-friendly URLs
- ✅ Markdown support
- ✅ Image uploads

## Installation

This module is automatically discovered by NeoFramework.

## Usage

### Display Blog Posts

```php
// In your controller
$posts = Post::latest()->paginate(10);
return view('blog::index', compact('posts'));
```

### Create a Post

```php
$post = Post::create([
    'title' => 'My First Post',
    'slug' => 'my-first-post',
    'content' => 'Post content here...',
    'author_id' => auth()->id(),
]);
```

## Routes

- `GET /blog` - List all posts
- `GET /blog/{id}` - Show single post
- `GET /admin/blog/posts` - Admin post list
- `POST /admin/blog/posts` - Create new post
- `PUT /admin/blog/posts/{id}` - Update post
- `DELETE /admin/blog/posts/{id}` - Delete post

## Configuration

Edit `config.php` to customize:

```php
return [
    'posts_per_page' => 10,
    'allow_comments' => true,
    'moderate_comments' => true,
];
```

## Database

Run migrations:

```bash
php neo migrate
```

## License

MIT
