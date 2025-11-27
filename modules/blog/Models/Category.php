<?php

namespace Modules\Blog\Models;

use NeoPhp\Database\Model;
use Modules\Blog\Models\Post;

/**
 * Category Model
 * 
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 */
class Category extends Model
{
    /**
     * The table associated with the model
     */
    protected string $table = 'categories';

    /**
     * The attributes that are mass assignable
     */
    protected array $fillable = [
        'name',
        'slug',
        'description',
    ];

    /**
     * The attributes that should be cast
     */
    protected array $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the category's posts
     */
    public function posts()
    {
        return $this->belongsToMany(
            Post::class,
            'post_category',
            'category_id',
            'post_id'
        );
    }

    /**
     * Get published posts count
     */
    public function getPublishedPostsCountAttribute(): int
    {
        return $this->posts()->published()->count();
    }
}
