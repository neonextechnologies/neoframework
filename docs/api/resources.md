# API Resources

## Introduction

API resources provide a transformation layer that sits between your Eloquent models and the JSON responses that are actually returned to your application's users. Resources allow you to expressively and easily transform your models and model collections into JSON.

## Generating Resources

```bash
php neo make:resource UserResource
php neo make:resource User --collection
php neo make:resource UserCollection
```

This creates `app/Http/Resources/UserResource.php`:

```php
<?php

namespace App\Http\Resources;

use NeoPhp\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at,
        ];
    }
}
```

## Resource Usage

### Returning Single Resource

```php
use App\Http\Resources\UserResource;

public function show($id)
{
    $user = User::findOrFail($id);

    return new UserResource($user);
}
```

Response:

```json
{
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "created_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

### Returning Resource Collections

```php
use App\Http\Resources\UserResource;

public function index()
{
    $users = User::all();

    return UserResource::collection($users);
}
```

Response:

```json
{
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
        },
        {
            "id": 2,
            "name": "Jane Smith",
            "email": "jane@example.com"
        }
    ]
}
```

## Resource Collections

### Creating Custom Collection

```php
<?php

namespace App\Http\Resources;

use NeoPhp\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->collection->count(),
            ],
        ];
    }
}
```

Usage:

```php
return new UserCollection(User::all());
```

## Conditional Attributes

### Using when() Method

```php
public function toArray($request)
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'email' => $this->email,
        'secret' => $this->when($request->user()->isAdmin(), 'secret-value'),
        'admin_data' => $this->when($request->user()->isAdmin(), [
            'role' => $this->role,
            'permissions' => $this->permissions,
        ]),
    ];
}
```

### Merging Conditional Attributes

```php
public function toArray($request)
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        $this->mergeWhen($request->user()->isAdmin(), [
            'first_secret' => 'value',
            'second_secret' => 'value',
        ]),
    ];
}
```

## Relationships

### Loading Relationships

```php
public function toArray($request)
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'posts' => PostResource::collection($this->whenLoaded('posts')),
        'latest_post' => new PostResource($this->whenLoaded('latestPost')),
    ];
}
```

### Conditional Pivot Information

```php
public function toArray($request)
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'expires_at' => $this->whenPivotLoaded('role_user', function () {
            return $this->pivot->expires_at;
        }),
    ];
}
```

## Adding Meta Data

### Resource Meta Data

```php
public function toArray($request)
{
    return [
        'id' => $this->id,
        'name' => $this->name,
    ];
}

public function with($request)
{
    return [
        'meta' => [
            'version' => '1.0',
            'author' => 'NeoFramework',
        ],
    ];
}
```

### Collection Meta Data

```php
public function toArray($request)
{
    return [
        'data' => $this->collection,
    ];
}

public function with($request)
{
    return [
        'meta' => [
            'total' => $this->collection->count(),
            'timestamp' => now(),
        ],
    ];
}
```

## Pagination

### Paginated Resources

```php
public function index()
{
    $users = User::paginate(15);

    return UserResource::collection($users);
}
```

Response:

```json
{
    "data": [...],
    "links": {
        "first": "http://example.com/api/users?page=1",
        "last": "http://example.com/api/users?page=10",
        "prev": null,
        "next": "http://example.com/api/users?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 10,
        "per_page": 15,
        "to": 15,
        "total": 150
    }
}
```

## Practical Examples

### Example 1: Blog API Resources

```php
<?php

// Post Resource
namespace App\Http\Resources;

use NeoPhp\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->when($request->routeIs('posts.show'), $this->content),
            'published_at' => $this->published_at->toIso8601String(),
            'author' => new UserResource($this->whenLoaded('author')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'comments_count' => $this->when(isset($this->comments_count), $this->comments_count),
            'is_published' => $this->published_at ? true : false,
            'reading_time' => $this->reading_time . ' min',
            'links' => [
                'self' => route('api.posts.show', $this->id),
                'comments' => route('api.posts.comments', $this->id),
            ],
        ];
    }

    public function with($request)
    {
        return [
            'meta' => [
                'version' => '1.0',
                'timestamp' => now()->toIso8601String(),
            ],
        ];
    }
}

// Post Collection Resource
namespace App\Http\Resources;

use NeoPhp\Http\Resources\Json\ResourceCollection;

class PostCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total_posts' => $this->collection->count(),
                'published' => $this->collection->where('published_at', '!=', null)->count(),
                'draft' => $this->collection->where('published_at', null)->count(),
            ],
        ];
    }
}

// Controller
namespace App\Http\Controllers\API;

use App\Http\Resources\PostResource;
use App\Http\Resources\PostCollection;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::with(['author', 'category', 'tags'])
            ->withCount('comments')
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->paginate(15);

        return PostResource::collection($posts);
    }

    public function show($id)
    {
        $post = Post::with(['author', 'category', 'tags', 'comments.user'])
            ->findOrFail($id);

        return new PostResource($post);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'category_id' => 'required|exists:categories,id',
        ]);

        $post = Post::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'category_id' => $validated['category_id'],
            'user_id' => auth()->id(),
        ]);

        return new PostResource($post->load(['author', 'category']));
    }
}
```

### Example 2: E-commerce Product API

```php
<?php

namespace App\Http\Resources;

use NeoPhp\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => [
                'amount' => $this->price,
                'currency' => 'USD',
                'formatted' => '$' . number_format($this->price, 2),
            ],
            'discount' => $this->when($this->discount_price, [
                'amount' => $this->discount_price,
                'percentage' => round((($this->price - $this->discount_price) / $this->price) * 100),
                'formatted' => '$' . number_format($this->discount_price, 2),
            ]),
            'stock' => [
                'quantity' => $this->stock,
                'available' => $this->stock > 0,
                'low_stock' => $this->stock > 0 && $this->stock < 10,
            ],
            'images' => $this->images->map(function ($image) {
                return [
                    'url' => $image->url,
                    'thumbnail' => $image->thumbnail_url,
                ];
            }),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'brand' => $this->when($this->brand_id, new BrandResource($this->whenLoaded('brand'))),
            'attributes' => $this->whenLoaded('attributes', function () {
                return $this->attributes->map(function ($attr) {
                    return [
                        'name' => $attr->name,
                        'value' => $attr->pivot->value,
                    ];
                });
            }),
            'reviews' => [
                'average_rating' => $this->when(isset($this->avg_rating), round($this->avg_rating, 1)),
                'count' => $this->when(isset($this->reviews_count), $this->reviews_count),
                'data' => ReviewResource::collection($this->whenLoaded('reviews')),
            ],
            'related_products' => ProductResource::collection($this->whenLoaded('relatedProducts')),
            'is_featured' => $this->featured,
            'is_new' => $this->created_at->gt(now()->subDays(30)),
            'links' => [
                'self' => route('api.products.show', $this->id),
                'add_to_cart' => route('api.cart.add', $this->id),
                'reviews' => route('api.products.reviews', $this->id),
            ],
        ];
    }
}

// Controller
class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['category', 'brand'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->when($request->category, function ($query, $category) {
                $query->where('category_id', $category);
            })
            ->when($request->min_price, function ($query, $minPrice) {
                $query->where('price', '>=', $minPrice);
            })
            ->when($request->max_price, function ($query, $maxPrice) {
                $query->where('price', '<=', $maxPrice);
            })
            ->when($request->sort === 'price_asc', function ($query) {
                $query->orderBy('price', 'asc');
            })
            ->when($request->sort === 'price_desc', function ($query) {
                $query->orderBy('price', 'desc');
            })
            ->paginate(20);

        return ProductResource::collection($products);
    }

    public function show($id)
    {
        $product = Product::with([
            'category',
            'brand',
            'attributes',
            'reviews.user',
            'relatedProducts',
        ])
        ->withAvg('reviews', 'rating')
        ->withCount('reviews')
        ->findOrFail($id);

        return new ProductResource($product);
    }
}
```

### Example 3: User Profile API

```php
<?php

namespace App\Http\Resources;

use NeoPhp\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->when($request->user()->id === $this->id, $this->email),
            'avatar' => $this->avatar_url,
            'bio' => $this->bio,
            'location' => $this->location,
            'website' => $this->website,
            'social_links' => $this->when($this->social_links, [
                'twitter' => $this->social_links['twitter'] ?? null,
                'github' => $this->social_links['github'] ?? null,
                'linkedin' => $this->social_links['linkedin'] ?? null,
            ]),
            'stats' => [
                'posts' => $this->when(isset($this->posts_count), $this->posts_count),
                'followers' => $this->when(isset($this->followers_count), $this->followers_count),
                'following' => $this->when(isset($this->following_count), $this->following_count),
            ],
            'is_following' => $this->when($request->user(), function () use ($request) {
                return $request->user()->isFollowing($this->id);
            }),
            'is_own_profile' => $request->user() && $request->user()->id === $this->id,
            'joined_at' => $this->created_at->diffForHumans(),
            'last_active' => $this->when($this->last_active_at, $this->last_active_at->diffForHumans()),
            $this->mergeWhen($request->user() && $request->user()->id === $this->id, [
                'settings' => [
                    'email_notifications' => $this->email_notifications,
                    'push_notifications' => $this->push_notifications,
                    'profile_visibility' => $this->profile_visibility,
                ],
            ]),
        ];
    }
}

// Controller
class UserProfileController extends Controller
{
    public function show($username)
    {
        $user = User::where('username', $username)
            ->withCount(['posts', 'followers', 'following'])
            ->firstOrFail();

        return new UserProfileResource($user);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'bio' => 'sometimes|string|max:500',
            'location' => 'sometimes|string|max:100',
            'website' => 'sometimes|url',
        ]);

        $user = auth()->user();
        $user->update($validated);

        return new UserProfileResource($user->loadCount(['posts', 'followers', 'following']));
    }
}
```

## Best Practices

### 1. Keep Resources Focused

```php
// Good - focused on API representation
class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
```

### 2. Use Conditional Loading

```php
public function toArray($request)
{
    return [
        'id' => $this->id,
        'posts' => PostResource::collection($this->whenLoaded('posts')),
    ];
}
```

### 3. Add Useful Meta Data

```php
public function with($request)
{
    return [
        'meta' => [
            'version' => '1.0',
            'timestamp' => now()->toIso8601String(),
        ],
    ];
}
```

### 4. Use Resource Collections

```php
return UserResource::collection($users);
// or
return new UserCollection($users);
```

### 5. Add Links for HATEOAS

```php
'links' => [
    'self' => route('api.users.show', $this->id),
    'posts' => route('api.users.posts', $this->id),
]
```

## Testing API Resources

```php
public function test_user_resource_returns_correct_data()
{
    $user = User::factory()->create();

    $resource = new UserResource($user);

    $this->assertEquals([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
    ], $resource->toArray(request()));
}

public function test_api_returns_paginated_users()
{
    User::factory()->count(30)->create();

    $response = $this->getJson('/api/users');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => ['*' => ['id', 'name', 'email']],
            'links',
            'meta',
        ]);
}
```

## Next Steps

- [Authentication](api-authentication.md) - API authentication
- [Rate Limiting](rate-limiting.md) - API rate limiting
- [Versioning](versioning.md) - API versioning
- [Testing](../testing/getting-started.md) - Test your APIs
