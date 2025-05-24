<?php

declare(strict_types=1);

namespace Patoui\ModelHistory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $model_type
 * @property string $model_id
 * @property array<string, mixed> $old
 * @property array<string, mixed> $new
 * @property int $auth_id
 * @property \Carbon\CarbonImmutable|string $created_at
 */
class ModelHistory extends Model
{
    protected $dateFormat = 'Y-m-d H:i:s.v';

    public const UPDATED_AT = null;

    protected $fillable = [
        'model_type',
        'model_id',
        'old',
        'new',
        'auth_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old' => 'array',
            'new' => 'array',
            'created_at' => 'immutable_datetime',
        ];
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
