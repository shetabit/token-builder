<?php

namespace Shetabit\TokenBuilder\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A token that can expire, by a date or by the number of times it was used.
 *
 * @property string $token
 * @property CarbonInterface|null $expired_at
 * @property int $usage_count
 * @property int $max_usage_limit
 * @property array<array-key, mixed>|null $data
 * @property string|null $type
 * @property CarbonInterface|null $deleted_at
 */
class Token extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tokens';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'token',
        'expired_at',
        'usage_count',
        'max_usage_limit',
        'data',
        'type',
    ];

    /**
     * Retrieve related model.
     *
     * @return MorphTo<Model, $this>
     */
    public function tokenable() : MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Mark token as used
     */
    public function use() : static
    {
        $this->increment('usage_count');

        return $this;
    }

    /**
     * Determine if token has used
     */
    public function hasUsed() : bool
    {
        return $this->usage_count > 0;
    }

    /**
     * Determine if token has expired
     */
    public function hasExpired() : bool
    {
        return $this->expired_at !== null && now()->gt($this->expired_at);
    }

    /**
     * Mark token as expired
     */
    public function markAsExpired() : static
    {
        $this->forceFill(['expired_at' => now()])->save();

        return $this;
    }

    /**
     * Determine usage limit is enabled.
     */
    public function hasMaxUsageLimit() : bool
    {
        return $this->max_usage_limit > 0;
    }

    /**
     * Determine usage limit has exceed.
     */
    public function hasExceedMaxUsage() : bool
    {
        return $this->hasMaxUsageLimit() && $this->usage_count >= $this->max_usage_limit;
    }

    /**
     * Determine token is valid.
     */
    public function isValid() : bool
    {
        return !$this->hasExpired() && !$this->hasExceedMaxUsage();
    }

    /**
     * Filter valid tokens
     *
     * @param Builder<static> $query
     * @return Builder<static>
     */
    public function scopeValid(Builder $query) : Builder
    {
        return $query
            ->where(function (Builder $query): void {
 // check usage limit
                $query
                    ->where('max_usage_limit', '=', 0)
                    ->orWhereColumn('usage_count', '<', 'max_usage_limit');
            })
            ->where(function (Builder $query): void {
 // check expiration time
                $query
                    ->whereNull('expired_at')
                    ->orWhere('expired_at', '>', now());
            });
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    protected function casts() : array
    {
        return [
            'token' => 'string',
            'expired_at' => 'datetime',
            'usage_count' => 'integer',
            'max_usage_limit' => 'integer',
            'data' => 'json',
        ];
    }
}
