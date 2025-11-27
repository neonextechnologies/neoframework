<?php

namespace Modules\Blog\Models;

use NeoPhp\Database\Model;
use App\Models\User;
use Modules\Blog\Models\Post;

/**
 * Comment Model
 * 
 * @property int $id
 * @property int $post_id
 * @property int $user_id
 * @property string $content
 * @property string $status
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 */
class Comment extends Model
{
    /**
     * The table associated with the model
     */
    protected string $table = 'comments';

    /**
     * The attributes that are mass assignable
     */
    protected array $fillable = [
        'post_id',
        'user_id',
        'content',
        'status',
    ];

    /**
     * The attributes that should be cast
     */
    protected array $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the comment's post
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    /**
     * Get the comment's author
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope: Get approved comments
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope: Get pending comments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
