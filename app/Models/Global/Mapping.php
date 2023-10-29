<?php

namespace App\Models\Global;

use Framework\Eloquent\Model;

/**
 * @property int $id
 * @property int $shard_id
 */
class Mapping extends Model
{
    protected ?string $table = 'mapping';

    protected array $fillable = [
        'id', // auto-increment
        'shard_id',
    ];

    protected array $casts = [
        'id' => 'int',
        'shard_id' => 'int',
    ];
}