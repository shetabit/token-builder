<?php

namespace Shetabit\TokenBuilder\Facade;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;
use Shetabit\TokenBuilder\Builder;
use Shetabit\TokenBuilder\Models\Token;

/**
 * The token builder, as a Laravel facade.
 *
 * @method static Builder setUniqueId(mixed $uniqueId)
 * @method static mixed getUniqueId()
 * @method static Builder setData(mixed $data)
 * @method static array<string, mixed> getData()
 * @method static Builder setExpireDate(\Carbon\CarbonInterface $date)
 * @method static \Carbon\CarbonInterface|null getExpireDate()
 * @method static Builder setUsageLimit(int $number)
 * @method static int getUsageLimit()
 * @method static Builder setType(string $type)
 * @method static string|null getType()
 * @method static Builder setRelatedItem(Model $item)
 * @method static Model|null getRelatedItem()
 * @method static Token build(int $length = 8)
 * @method static Token create(int $length = 8)
 * @method static Token|null findToken()
 * @method static Token|null findValidToken()
 * @method static bool isValid()
 * @method static bool isInvalid()
 * @method static bool isNotValid()
 *
 * @see Builder
 */
class TokenBuilder extends Facade
{
    /**
     * The name the builder is bound to in the service container.
     */
    public const string SERVICE_NAME = 'shetabit-token-builder';

    /**
     * Get the registered name of the component.
     */
    public static function getFacadeAccessor() : string
    {
        return self::SERVICE_NAME;
    }

    /**
     * Get the object behind the facade.
     *
     * A builder carries the token it is building, so every access starts a new
     * one: the instance a facade keeps would hand the settings of a token over
     * to the next one that is built in the same request.
     */
    public static function getFacadeRoot() : mixed
    {
        return static::$app->make(static::getFacadeAccessor());
    }
}
