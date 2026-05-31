<?php

namespace App\Models;

use Phare\Eloquent\Concerns\HasTimestamps;
use Phare\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $body
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 */
class Post extends Model
{
    use HasTimestamps;

    protected ?string $table = 'posts';

    protected array $fillable = [
        'user_id',
        'title',
        'body',
    ];

    protected array $casts = [
        'id' => 'int',
        'user_id' => 'int',
    ];

    /**
     * A post belongs to one user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
