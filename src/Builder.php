<?php

namespace Shetabit\TokenBuilder;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use RuntimeException;
use Shetabit\TokenBuilder\Models\Token;
use Shetabit\TokenBuilder\Repositories\TokensRepository;
use Shetabit\TokenBuilder\Traits\Concerns\Validation;
use Shetabit\TokenBuilder\Traits\HasTemporaryTokens;

class Builder
{
    use Validation;

    /**
     * The token itself, when it is not generated but given.
     */
    protected mixed $uniqueId = null;

    /**
     * The moment the token expires.
     */
    protected CarbonInterface|null $expiredAt = null;

    /**
     * How often the token may be used.
     */
    protected int $usageLimit = 0;

    /**
     * The scope of the token.
     */
    protected string|null $type = null;

    /**
     * The model the token belongs to.
     */
    protected Model|null $relatedItem = null;

    /**
     * Data that is kept with the token.
     *
     * @var array<array-key, mixed>
     */
    protected array $data = [];

    /**
     * Tokens repository
     */
    protected TokensRepository $tokensRepository;

    public function __construct()
    {
        $this->tokensRepository = app(TokensRepository::class);
    }

    /**
     * Set token
     */
    public function setUniqueId(mixed $uniqueId) : static
    {
        $this->uniqueId = $uniqueId;

        return $this;
    }

    /**
     * Retrieve uniqueId
     */
    public function getUniqueId() : mixed
    {
        return $this->uniqueId;
    }

    /**
     * Set data
     *
     * It takes the data as an array, or as one argument per value.
     */
    public function setData(mixed $data, mixed ...$rest) : static
    {
        $this->data = $rest === [] && is_array($data) ? $data : [$data, ...$rest];

        return $this;
    }

    /**
     * Retrieve data
     *
     * @return array<array-key, mixed>
     */
    public function getData() : array
    {
        return $this->data;
    }

    /**
     * Set expiration date
     */
    public function setExpireDate(CarbonInterface $date) : static
    {
        $this->expiredAt = $date;

        return $this;
    }

    /**
     * Retrieve expiration date
     */
    public function getExpireDate() : CarbonInterface|null
    {
        return $this->expiredAt;
    }

    /**
     * Set max usage count
     */
    public function setUsageLimit(int $number) : static
    {
        $this->usageLimit = $number;

        return $this;
    }

    /**
     * Retrieve max usage count
     */
    public function getUsageLimit() : int
    {
        return $this->usageLimit;
    }

    /**
     * Set type
     */
    public function setType(string $type) : static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Retrieve type
     */
    public function getType() : string|null
    {
        return $this->type;
    }

    /**
     * Set related Eloquent Model instance
     */
    public function setRelatedItem(Model $item) : static
    {
        $this->relatedItem = $item;

        return $this;
    }

    /**
     * Retrieve related Eloquent Model instance
     */
    public function getRelatedItem() : Model|null
    {
        return $this->relatedItem;
    }

    /**
     * Create a new token
     *
     * @param int $length the number of digits of a generated token
     */
    public function build(int $length = 8) : Token
    {
        $tokenData = [
            'token' => $this->getUniqueId() ?? $this->generateRandomInt($length),
            'expired_at' => $this->getExpireDate(),
            'max_usage_limit' => $this->getUsageLimit(),
            'type' => $this->getType(),
            'data' => $this->getData(),
        ];

        $relatedItem = $this->getRelatedItem();

        if ($relatedItem === null) {
            return $this->tokensRepository->create($tokenData);
        }

        if (!method_exists($relatedItem, 'temporaryTokens')) {
            throw new RuntimeException(
                $relatedItem::class.' has to use the '.HasTemporaryTokens::class.' trait to carry tokens.'
            );
        }

        $token = $relatedItem->temporaryTokens()->create($tokenData);

        return $token instanceof Token
            ? $token
            : throw new RuntimeException($relatedItem::class.'::temporaryTokens() does not answer with tokens.');
    }

    /**
     * Alias build
     *
     * @alias build
     */
    public function create(int $length = 8) : Token
    {
        return $this->build($length);
    }

    /**
     * Retrieve a token.
     */
    public function findToken() : Token|null
    {
        $token = $this->getUniqueId();

        if ($token === null || $token === '') {
            return null;
        }

        return $this->repository()->findToken($token, $this->getType(), $this->getRelatedItem());
    }

    /**
     * The repository the tokens are read from and written to.
     */
    protected function repository() : TokensRepository
    {
        return $this->tokensRepository;
    }

    /**
     * Generate a random token of the given number of digits
     */
    private function generateRandomInt(int $length) : int
    {
        if ($length < 1 || $length > 18) {
            throw new InvalidArgumentException('A token can be 1 to 18 digits long, '.$length.' given.');
        }

        return random_int($length === 1 ? 0 : 10 ** ($length - 1), (10 ** $length) - 1);
    }
}
