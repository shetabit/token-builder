<?php

namespace Shetabit\TokenBuilder\Tests\Unit;

use Shetabit\TokenBuilder\Builder;
use Shetabit\TokenBuilder\Tests\TestCase;

final class ValidationTest extends TestCase
{
    public function testAFreshTokenIsValid() : void
    {
        $token = new Builder()->setUsageLimit(1)->build();

        $builder = new Builder()->setUniqueId($token->token);

        $this->assertTrue($builder->isValid());
        $this->assertFalse($builder->isInvalid());
        $this->assertFalse($builder->isNotValid());
        $this->assertTrue($token->is($builder->findValidToken()));
    }

    public function testAUsedUpTokenIsNotValid() : void
    {
        $token = new Builder()->setUsageLimit(1)->build();
        $token->use();

        $builder = new Builder()->setUniqueId($token->token);

        $this->assertFalse($builder->isValid());
        $this->assertTrue($builder->isInvalid());
        $this->assertTrue($builder->isNotValid());
        $this->assertNull($builder->findValidToken());

        // It is still there, it just can not be used any more.
        $this->assertNotNull($builder->findToken());
    }

    public function testAnExpiredTokenIsNotValid() : void
    {
        $token = new Builder()->setExpireDate(now()->addMinutes(5))->build();
        $builder = new Builder()->setUniqueId($token->token);

        $this->assertTrue($builder->isValid());

        $this->travelTo(now()->addMinutes(6));

        $this->assertFalse($builder->isValid());
    }

    public function testATokenOfAnotherTypeIsNotValidHere() : void
    {
        $token = new Builder()->setType('login')->build();

        $this->assertFalse(new Builder()->setUniqueId($token->token)->setType('signup')->isValid());
        $this->assertTrue(new Builder()->setUniqueId($token->token)->setType('login')->isValid());
    }

    public function testATokenThatDoesNotExistIsNotValid() : void
    {
        $this->assertFalse(new Builder()->setUniqueId('a-token-that-was-never-built')->isValid());
        $this->assertFalse(new Builder()->isValid());
    }
}
