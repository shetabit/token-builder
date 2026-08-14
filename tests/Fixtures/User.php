<?php

namespace Shetabit\TokenBuilder\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Shetabit\TokenBuilder\Traits\HasTemporaryTokens;

/**
 * A model that carries tokens, the way an application would declare one.
 *
 * @property string $name
 */
class User extends Model
{
    use HasTemporaryTokens;

    /**
     * @var list<string>
     */
    protected $fillable = ['name'];
}
