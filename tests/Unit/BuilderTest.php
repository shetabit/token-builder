<?php

namespace Shetabit\TokenBuilder\Tests\Unit;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use InvalidArgumentException;
use RuntimeException;
use Shetabit\TokenBuilder\Builder;
use Shetabit\TokenBuilder\Models\Token;
use Shetabit\TokenBuilder\Tests\Fixtures\Post;
use Shetabit\TokenBuilder\Tests\TestCase;

final class BuilderTest extends TestCase
{
    public function testABuilderStartsOutEmpty() : void
    {
        $builder = new Builder();

        $this->assertNull($builder->getUniqueId());
        $this->assertNull($builder->getExpireDate());
        $this->assertNull($builder->getType());
        $this->assertNull($builder->getRelatedItem());
        $this->assertSame(0, $builder->getUsageLimit());
        $this->assertSame([], $builder->getData());
    }

    public function testEverythingItIsGivenCanBeReadBack() : void
    {
        $date = Carbon::parse('2030-01-01 10:00:00');
        $user = $this->createUser();

        $builder = new Builder()
            ->setUniqueId('a-token')
            ->setExpireDate($date)
            ->setUsageLimit(3)
            ->setType('login')
            ->setRelatedItem($user);

        $this->assertSame('a-token', $builder->getUniqueId());
        $this->assertTrue($date->eq($builder->getExpireDate()));
        $this->assertSame(3, $builder->getUsageLimit());
        $this->assertSame('login', $builder->getType());
        $this->assertTrue($user->is($builder->getRelatedItem()));
    }

    public function testAnImmutableDateIsAnExpirationDateToo() : void
    {
        $date = CarbonImmutable::parse('2030-01-01 10:00:00');

        $this->assertTrue($date->eq(new Builder()->setExpireDate($date)->getExpireDate()));
    }

    public function testDataIsTakenAsAnArrayOrAsOneArgumentPerValue() : void
    {
        $this->assertSame(['name' => 'Mahdi'], new Builder()->setData(['name' => 'Mahdi'])->getData());
        $this->assertSame(['a', 'b'], new Builder()->setData('a', 'b')->getData());
        $this->assertSame(['a'], new Builder()->setData('a')->getData());
    }

    public function testItBuildsATokenOfTheGivenLength() : void
    {
        $token = new Builder()->build(4);

        $this->assertInstanceOf(Token::class, $token);
        $this->assertMatchesRegularExpression('/^[0-9]{4}$/', $token->token);
    }

    public function testItBuildsATokenOfEightDigitsByDefault() : void
    {
        $this->assertMatchesRegularExpression('/^[0-9]{8}$/', new Builder()->build()->token);
    }

    public function testTheAliasOfBuildTakesTheLengthAsWell() : void
    {
        // `create()` used to build a token of the default length, whatever it
        // was called with.
        $this->assertMatchesRegularExpression('/^[0-9]{6}$/', new Builder()->create(6)->token);
    }

    public function testItRefusesALengthItCanNotBuildATokenOf() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A token can be 1 to 18 digits long, 19 given.');

        new Builder()->build(19);
    }

    public function testAGivenTokenIsUsedInsteadOfAGeneratedOne() : void
    {
        $this->assertSame('jgaZ1z9', new Builder()->setUniqueId('jgaZ1z9')->build()->token);
    }

    public function testItStoresEverythingTheTokenWasGiven() : void
    {
        $date = Carbon::parse('2030-01-01 10:00:00');

        $token = new Builder()
            ->setExpireDate($date)
            ->setUsageLimit(3)
            ->setType('login')
            ->setData(['mobile' => '9373620353'])
            ->build();

        $stored = Token::query()->whereKey($token->getKey())->sole();

        $this->assertTrue($date->eq($stored->expired_at));
        $this->assertSame(3, $stored->max_usage_limit);
        $this->assertSame('login', $stored->type);
        $this->assertSame(['mobile' => '9373620353'], $stored->data);
        $this->assertSame(0, $stored->usage_count);
    }

    public function testATokenCanBelongToAModel() : void
    {
        $user = $this->createUser();

        $token = new Builder()->setRelatedItem($user)->build();

        $this->assertTrue($user->is($token->tokenable));
        $this->assertSame(1, $user->temporaryTokens()->count());
    }

    public function testItRefusesAModelThatCanNotCarryTokens() : void
    {
        $post = Post::query()->create(['title' => 'A post']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('has to use the '.\Shetabit\TokenBuilder\Traits\HasTemporaryTokens::class);

        new Builder()->setRelatedItem($post)->build();
    }

    public function testItFindsATokenItBuilt() : void
    {
        $token = new Builder()->build();

        $found = new Builder()->setUniqueId($token->token)->findToken();

        $this->assertTrue($token->is($found));
    }

    public function testItFindsNothingWithoutATokenToLookFor() : void
    {
        new Builder()->build();

        $this->assertNull(new Builder()->findToken());
        $this->assertNull(new Builder()->setUniqueId('')->findToken());
        $this->assertNull(new Builder()->findValidToken());
    }

    public function testATokenIsOnlyFoundInItsOwnType() : void
    {
        $token = new Builder()->setType('login')->build();

        $this->assertNull(new Builder()->setUniqueId($token->token)->setType('signup')->findToken());
        $this->assertNotNull(new Builder()->setUniqueId($token->token)->setType('login')->findToken());
        $this->assertNotNull(new Builder()->setUniqueId($token->token)->findToken());
    }

    public function testATokenOfAModelIsOnlyFoundThroughThatModel() : void
    {
        $user = $this->createUser();
        $other = $this->createUser('Someone else');

        $token = new Builder()->setRelatedItem($user)->build();

        $this->assertNotNull(new Builder()->setRelatedItem($user)->setUniqueId($token->token)->findToken());
        $this->assertNull(new Builder()->setRelatedItem($other)->setUniqueId($token->token)->findToken());
    }
}
