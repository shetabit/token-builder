<?php

namespace Shetabit\TokenBuilder\Tests\Unit;

use Shetabit\TokenBuilder\Tests\TestCase;

final class HasTemporaryTokensTest extends TestCase
{
    public function testItBuildsTokensOfTheModelItIsUsedIn() : void
    {
        $user = $this->createUser();

        $token = $user->temporaryTokenBuilder()->build();

        $this->assertTrue($user->is($token->tokenable));
    }

    public function testAModelWithoutTokensHasNoValidOnes() : void
    {
        $user = $this->createUser();

        $this->assertFalse($user->hasValidTemporaryToken());
        $this->assertFalse($user->hasValidTemporaryToken('reset-password'));
        $this->assertCount(0, $user->validTemporaryTokens());
    }

    public function testItAnswersWithTheValidTokensOfTheModel() : void
    {
        $user = $this->createUser();
        $other = $this->createUser('Someone else');

        $reset = $user->temporaryTokenBuilder()->setType('reset-password')->setUsageLimit(1)->build();
        $login = $user->temporaryTokenBuilder()->setType('login')->build();
        $other->temporaryTokenBuilder()->setType('reset-password')->build();

        $this->assertTrue($user->hasValidTemporaryToken());
        $this->assertTrue($user->hasValidTemporaryToken('reset-password'));
        $this->assertCount(2, $user->validTemporaryTokens());
        $this->assertSame([$reset->getKey()], $user->validTemporaryTokens('reset-password')->modelKeys());
        $this->assertSame([$login->getKey()], $user->validTemporaryTokens('login')->modelKeys());
    }

    public function testAUsedUpTokenIsNoValidTokenOfTheModel() : void
    {
        $user = $this->createUser();

        $token = $user->temporaryTokenBuilder()->setType('reset-password')->setUsageLimit(1)->build();

        $this->assertTrue($user->hasValidTemporaryToken('reset-password'));

        $token->use();

        $this->assertFalse($user->hasValidTemporaryToken('reset-password'));
        $this->assertFalse($user->hasValidTemporaryToken());
        $this->assertCount(0, $user->validTemporaryTokens('reset-password'));

        // It is still there, it just can not be used any more.
        $this->assertSame(1, $user->temporaryTokens()->count());
    }

    public function testAnExpiredTokenIsNoValidTokenOfTheModel() : void
    {
        $user = $this->createUser();

        $user->temporaryTokenBuilder()->setType('reset-password')->setExpireDate(now()->addMinutes(5))->build();

        $this->assertTrue($user->hasValidTemporaryToken('reset-password'));

        $this->travelTo(now()->addMinutes(6));

        $this->assertFalse($user->hasValidTemporaryToken('reset-password'));
        $this->assertCount(0, $user->validTemporaryTokens());
    }

    public function testAnEmptyTypeLooksAtEveryTokenOfTheModel() : void
    {
        $user = $this->createUser();

        $user->temporaryTokenBuilder()->setType('reset-password')->build();

        $this->assertTrue($user->hasValidTemporaryToken(''));
        $this->assertCount(1, $user->validTemporaryTokens(''));
    }
}
