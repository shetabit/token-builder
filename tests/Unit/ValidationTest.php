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

    public function testItTellsWhetherAModelStillHasAValidToken() : void
    {
        $user = $this->createUser();

        $builder = new Builder()->setRelatedItem($user)->setType('reset-password');

        $this->assertFalse($builder->hasAnyValidToken());
        $this->assertCount(0, $builder->findValidTokens());

        $token = $builder->setUsageLimit(1)->build();

        $this->assertTrue($builder->hasAnyValidToken());
        $this->assertSame([$token->getKey()], $builder->findValidTokens()->modelKeys());

        $token->use();

        $this->assertFalse($builder->hasAnyValidToken());
        $this->assertCount(0, $builder->findValidTokens());
    }

    public function testTheValidTokensOfAModelAreItsOwnOnesOfItsOwnType() : void
    {
        $user = $this->createUser();
        $other = $this->createUser('Someone else');

        $token = new Builder()->setRelatedItem($user)->setType('reset-password')->build();
        new Builder()->setRelatedItem($other)->setType('reset-password')->build();

        $this->assertSame(
            [$token->getKey()],
            new Builder()->setRelatedItem($user)->setType('reset-password')->findValidTokens()->modelKeys()
        );
        $this->assertFalse(new Builder()->setRelatedItem($user)->setType('login')->hasAnyValidToken());

        // Without a related model, the tokens of everyone are looked at.
        $this->assertCount(2, new Builder()->setType('reset-password')->findValidTokens());
        $this->assertTrue(new Builder()->hasAnyValidToken());
    }

    public function testTheUniqueIdIsNoPartOfTheLookupOfEveryValidToken() : void
    {
        $user = $this->createUser();
        new Builder()->setRelatedItem($user)->build();

        $builder = new Builder()->setRelatedItem($user)->setUniqueId('a-token-that-was-never-built');

        $this->assertNull($builder->findValidToken());
        $this->assertTrue($builder->hasAnyValidToken());
        $this->assertCount(1, $builder->findValidTokens());
    }
}
