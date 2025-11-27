<?php

namespace Modules\Blog\Models;

use NeoPhp\Database\Model;
use NeoPhp\Database\Concerns\SoftDeletes;
use App\Models\User;
use Modules\Blog\Models\Comment;
use Modules\Blog\Models\Category;

/**
 * Post Model
 * 
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $content
 * @property string $excerpt
 * @property int $author_id
 * @property string $status
 * @property \DateTime $published_at
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 * @property \DateTime $deleted_at
 */
class Post extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model
     */
    protected string $table = 'posts';

    /**
     * The attributes that are mass assignable
     */
    protected array $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'author_id',
        'status',
        'published_at',
    ];

    /**
     * The attributes that should be hidden
     */
    protected array $hidden = [
        'deleted_at',
    ];

    /**
     * The attributes that should be cast
     */
    protected array $casts = [
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The relationships that should always be loaded
     */
    protected array $with = [];

    /**
     * Boot the model
     */
    protected static function boot(): void
    {
        parent::boot();

        // Model event: Auto-generate slug when creating
        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = static::generateSlug($post->title);
            }
        });

        // Model event: Log when post is published
        static::updating(function ($post) {
            if ($post->isDirty('status') && $post->status === 'published') {
                logger()->info("Post '{$post->title}' published", ['post_id' => $post->id]);
            }
        });

        // Model event: Clean up related data when deleting
        static::deleting(function ($post) {
            // Delete all comments when post is deleted
            $post->comments()->delete();
        });
    }

    /**
     * Relationships
     */

    /**
     * Get the post's author
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the post's comments
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id');
    }

    /**
     * Get the post's categories
     */
    public function categories()
    {
        return $this->belongsToMany(
            Category::class,
            'post_category',
            'post_id',
            'category_id'
        )->withTimestamps();
    }

    /**
     * Query Scopes
     */

    /**
     * Scope: Get only published posts
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('published_at', '<=', date('Y-m-d H:i:s'));
    }

    /**
     * Scope: Get draft posts
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope: Get posts by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Get recent posts
     */
    public function scopeRecent($query, int $limit = 5)
    {
        return $query->published()
                    ->orderBy('published_at', 'desc')
                    ->limit($limit);
    }

    /**
     * Scope: Search posts by title or content
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('content', 'like', "%{$term}%");
        });
    }

    /**
     * Accessors & Mutators
     */

    /**
     * Get the excerpt
     */
    public function getExcerptAttribute($value): string
    {
        if (!empty($value)) {
            return $value;
        }

        // Auto-generate excerpt from content
        return substr(strip_tags($this->content), 0, 200) . '...';
    }

    /**
     * Set the title
     */
    public function setTitleAttribute($value): void
    {
        $this->attributes['title'] = ucfirst($value);
    }

    /**
     * Helper Methods
     */

    /**
     * Check if post is published
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' 
            && $this->published_at 
            && $this->published_at <= now();
    }

    /**
     * Check if post is draft
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Publish the post
     */
    public function publish(): bool
    {
        $this->status = 'published';
        $this->published_at = $this->published_at ?? now();
        
        return $this->save();
    }

    /**
     * Unpublish the post (set to draft)
     */
    public function unpublish(): bool
    {
        $this->status = 'draft';
        
        return $this->save();
    }

    /**
     * Generate a unique slug from title
     */
    protected static function generateSlug(string $title): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        
        // Check if slug exists and make it unique
        $count = static::where('slug', 'like', $slug . '%')->count();
        
        return $count > 0 ? $slug . '-' . ($count + 1) : $slug;
    }
}

