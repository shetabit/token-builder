<?php

namespace Shetabit\TokenBuilder\Tests\Unit;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Shetabit\TokenBuilder\Builder;
use Shetabit\TokenBuilder\Models\Token;
use Shetabit\TokenBuilder\Tests\TestCase;

final class TokenTest extends TestCase
{
    public function testTheExpirationDateIsADate() : void
    {
        $token = new Builder()->setExpireDate(now()->addMinutes(5))->build();

        // Laravel 11 dropped the `$dates` property this used to be declared in,
        // which left the date a plain string.
        $this->assertInstanceOf(CarbonInterface::class, $token->fresh()?->expired_at);
    }

    public function testATokenWithoutAnExpirationDateNeverExpires() : void
    {
        $token = new Builder()->build();

        $this->assertNull($token->expired_at);
        $this->assertFalse($token->hasExpired());
    }

    public function testATokenExpiresAfterItsDate() : void
    {
        $token = new Builder()->setExpireDate(now()->addMinutes(5))->build();

        $this->assertFalse($token->hasExpired());

        $this->travelTo(now()->addMinutes(6));

        $this->assertTrue($token->hasExpired());
    }

    public function testATokenCanBeMarkedAsExpired() : void
    {
        $token = new Builder()->setExpireDate(now()->addYear())->build();

        $token->markAsExpired();

        $this->assertTrue($token->hasExpired());
        $this->assertTrue($token->fresh()?->hasExpired());
    }

    public function testUsingATokenCountsUp() : void
    {
        $token = new Builder()->build();

        $this->assertFalse($token->hasUsed());

        $token->use();

        $this->assertSame(1, $token->usage_count);
        $this->assertTrue($token->hasUsed());
        $this->assertSame(1, $token->fresh()?->usage_count);
    }

    public function testATokenWithoutAUsageLimitCanBeUsedForever() : void
    {
        $token = new Builder()->setUsageLimit(0)->build();

        $token->use()->use()->use();

        $this->assertFalse($token->hasMaxUsageLimit());
        $this->assertFalse($token->hasExceedMaxUsage());
        $this->assertTrue($token->isValid());
    }

    public function testATokenIsUsedUpAfterItsLimit() : void
    {
        $token = new Builder()->setUsageLimit(2)->build();

        $this->assertTrue($token->hasMaxUsageLimit());

        $token->use();

        $this->assertFalse($token->hasExceedMaxUsage());
        $this->assertTrue($token->isValid());

        $token->use();

        $this->assertTrue($token->hasExceedMaxUsage());
        $this->assertFalse($token->isValid());
    }

    public function testAnExpiredTokenIsNotValid() : void
    {
        $token = new Builder()->setExpireDate(now()->subMinute())->build();

        $this->assertFalse($token->isValid());
    }

    public function testAGeneratedTokenIsAString() : void
    {
        // The column is a string, so a token that was just built answers the
        // same way as one that was read back from the database.
        $token = new Builder()->build();

        $this->assertMatchesRegularExpression('/^[0-9]{8}$/', $token->token);
        $this->assertSame($token->token, $token->fresh()?->token);
    }

    public function testTheValidScopeOnlyFindsTokensThatCanStillBeUsed() : void
    {
        $valid = new Builder()->setUsageLimit(2)->build();
        $usedUp = new Builder()->setUsageLimit(1)->build()->use();
        $expired = new Builder()->setExpireDate(now()->subMinute())->build();
        $endless = new Builder()->setUsageLimit(0)->build();

        $found = Token::query()->valid()->pluck('token')->all();

        $this->assertContains($valid->token, $found);
        $this->assertContains($endless->token, $found);
        $this->assertNotContains($usedUp->token, $found);
        $this->assertNotContains($expired->token, $found);
    }

    public function testATokenKnowsWhatItBelongsTo() : void
    {
        $user = $this->createUser();

        $token = new Builder()->setRelatedItem($user)->build();

        $this->assertInstanceOf(MorphTo::class, $token->tokenable());
        $this->assertTrue($user->is($token->tokenable));
    }

    public function testATokenWithoutARelationBelongsToNothing() : void
    {
        $this->assertNull(new Builder()->build()->tokenable);
    }

    public function testADeletedTokenIsKeptButNoLongerFound() : void
    {
        $token = new Builder()->build();

        $token->delete();

        $this->assertNotNull($token->deleted_at);
        $this->assertNull(Token::query()->whereKey($token->getKey())->first());
        $this->assertNotNull(Token::withTrashed()->whereKey($token->getKey())->first());
        $this->assertNull(new Builder()->setUniqueId($token->token)->findToken());
    }

    public function testTheDataOfATokenIsStoredAsJson() : void
    {
        $token = new Builder()->setData(['mobile' => '9373620353', 'name' => 'John Doe'])->build();

        $this->assertSame(['mobile' => '9373620353', 'name' => 'John Doe'], $token->fresh()?->data);
    }
}
