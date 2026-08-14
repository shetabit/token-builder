<?php

namespace Shetabit\TokenBuilder\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

/**
 * A model without the trait of the package.
 *
 * @property string $title
 */
class Post extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = ['title'];
}
