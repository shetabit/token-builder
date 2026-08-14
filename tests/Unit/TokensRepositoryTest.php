<?php

namespace Shetabit\TokenBuilder\Tests\Unit;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Shetabit\TokenBuilder\Abstracts\RepositoryAbstract;
use Shetabit\TokenBuilder\Builder;
use Shetabit\TokenBuilder\Models\Token;
use Shetabit\TokenBuilder\Repositories\TokensRepository;
use InvalidArgumentException;
use Shetabit\TokenBuilder\Tests\Fixtures\NotAModel;
use Shetabit\TokenBuilder\Tests\Fixtures\Post;
use Shetabit\TokenBuilder\Traits\HasTemporaryTokens;
use Shetabit\TokenBuilder\Tests\TestCase;

final class TokensRepositoryTest extends TestCase
{
    public function testItWorksWithTheTokenModel() : void
    {
        $repository = new TokensRepository();

        $this->assertSame(Token::class, $repository->model());
        $this->assertContains('token', $repository->getFillable());
    }

    public function testItCreatesFindsUpdatesAndDeletesAToken() : void
    {
        $repository = new TokensRepository();

        $token = $repository->create(['token' => 'a-token', 'max_usage_limit' => 1]);

        $this->assertTrue($token->is($repository->find($token->getKey())));

        $repository->update($token, ['type' => 'login']);

        $this->assertSame('login', $repository->find($token->getKey())?->type);

        $this->assertTrue($repository->delete($token));
        $this->assertNull($repository->find($token->getKey()));
    }

    public function testItForwardsTheMethodsOfTheModel() : void
    {
        new Builder()->setUniqueId('a-token')->build();

        $repository = new TokensRepository();

        // @phpstan-ignore method.notFound (the repository forwards to its model)
        $this->assertSame(1, $repository->newQuery()->count());
    }

    public function testARepositoryOfSomethingThatIsNoModelIsRefused() : void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('must be an instance of Model');

        new class () extends RepositoryAbstract {
            /**
             * @return class-string<Model>
             */
            public function model() : string
            {
                // @phpstan-ignore return.type (that is what is asserted)
                return NotAModel::class;
            }
        };
    }

    public function testItFindsTheTokenOfAModel() : void
    {
        $user = $this->createUser();
        $token = new Builder()->setRelatedItem($user)->setType('login')->build();

        $repository = new TokensRepository();

        $this->assertTrue($token->is($repository->findToken($token->token)));
        $this->assertTrue($token->is($repository->findToken($token->token, 'login', $user)));
        $this->assertNull($repository->findToken($token->token, 'signup', $user));
        $this->assertNull($repository->findToken('a-token-that-was-never-built'));
    }

    public function testItOnlyFindsAValidTokenWithFindValidToken() : void
    {
        $token = new Builder()->setUsageLimit(1)->build();

        $repository = new TokensRepository();

        $this->assertTrue($token->is($repository->findValidToken($token->token)));

        $token->use();

        $this->assertNull($repository->findValidToken($token->token));
        $this->assertNotNull($repository->findToken($token->token));
    }

    public function testItFindsEveryValidTokenOfAModel() : void
    {
        $user = $this->createUser();
        $other = $this->createUser('Someone else');

        $login = new Builder()->setRelatedItem($user)->setType('login')->setUsageLimit(1)->build();
        $signup = new Builder()->setRelatedItem($user)->setType('signup')->build();
        new Builder()->setRelatedItem($other)->setType('login')->build();

        $repository = new TokensRepository();

        $this->assertSame(3, $repository->findValidTokens()->count());
        $this->assertSame(2, $repository->findValidTokens(null, $user)->count());
        $this->assertSame([$login->getKey()], $repository->findValidTokens('login', $user)->modelKeys());
        $this->assertSame([$signup->getKey()], $repository->findValidTokens('signup', $user)->modelKeys());

        $login->use();

        $this->assertSame([], $repository->findValidTokens('login', $user)->modelKeys());
        $this->assertSame([$signup->getKey()], $repository->findValidTokens(null, $user)->modelKeys());
    }

    public function testItAnswersWhetherThereIsAnyValidTokenLeft() : void
    {
        $user = $this->createUser();
        $token = new Builder()->setRelatedItem($user)->setType('login')->setUsageLimit(1)->build();

        $repository = new TokensRepository();

        $this->assertTrue($repository->hasAnyValidToken());
        $this->assertTrue($repository->hasAnyValidToken(null, $user));
        $this->assertTrue($repository->hasAnyValidToken('login', $user));
        $this->assertFalse($repository->hasAnyValidToken('signup', $user));
        $this->assertFalse($repository->hasAnyValidToken(null, $this->createUser('Someone else')));

        $token->use();

        $this->assertFalse($repository->hasAnyValidToken('login', $user));
    }

    public function testAnExpiredTokenIsNoValidTokenOfAModelEither() : void
    {
        $user = $this->createUser();
        new Builder()->setRelatedItem($user)->setExpireDate(now()->addMinutes(5))->build();

        $repository = new TokensRepository();

        $this->assertTrue($repository->hasAnyValidToken(null, $user));

        $this->travelTo(now()->addMinutes(6));

        $this->assertFalse($repository->hasAnyValidToken(null, $user));
        $this->assertSame(0, $repository->findValidTokens(null, $user)->count());
    }

    public function testItRefusesAModelThatCanNotCarryTokens() : void
    {
        $post = Post::query()->create(['title' => 'A post']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('has to use the '.HasTemporaryTokens::class);

        new TokensRepository()->findToken('a-token', null, $post);
    }

    public function testTheRelationOfATokenIsLoadedWithIt() : void
    {
        $user = $this->createUser();
        $token = new Builder()->setRelatedItem($user)->build();

        $found = new TokensRepository()->findToken($token->token);

        $this->assertTrue($found?->relationLoaded('tokenable'));
        $this->assertInstanceOf(Model::class, $found->tokenable);
    }
}
