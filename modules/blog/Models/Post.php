<?php

namespace Modules\Blog\Models;

use NeoPhp\Database\Model;

/**
 * Post Model
 * 
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $content
 * @property int $author_id
 * @property string $status
 * @property \DateTime $published_at
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 */
class Post extends Model
{
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
     * The attributes that should be cast
     */
    protected array $casts = [
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the post's author
     * 
     * @return \NeoPhp\Database\Relations\BelongsTo
     */
    public function author()
    {
        // Will be implemented with advanced ORM
        // return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the post's comments
     * 
     * @return \NeoPhp\Database\Relations\HasMany
     */
    public function comments()
    {
        // Will be implemented with advanced ORM
        // return $this->hasMany(Comment::class);
    }

    /**
     * Get the post's categories
     * 
     * @return \NeoPhp\Database\Relations\BelongsToMany
     */
    public function categories()
    {
        // Will be implemented with advanced ORM
        // return $this->belongsToMany(Category::class);
    }

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
}
