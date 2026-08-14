<?php

namespace Shetabit\TokenBuilder\Tests\Feature;

use Shetabit\TokenBuilder\Facade\TokenBuilder;
use Shetabit\TokenBuilder\Models\Token;
use Shetabit\TokenBuilder\Tests\TestCase;

/**
 * The flows of the readme, through the facade an application would use.
 */
final class BuildingTokensTest extends TestCase
{
    public function testAnSmsPinIsBuiltUsedAndThenNoLongerValid() : void
    {
        // "will be expired after 1 usage"
        $token = TokenBuilder::setUsageLimit(1)->setType('sms-verification')->build(6);

        $this->assertMatchesRegularExpression('/^[0-9]{6}$/', $token->token);

        $sent = $token->token;

        // Somewhere else in the application, the pin comes back from the user.
        $builder = TokenBuilder::setUniqueId($sent)->setType('sms-verification');

        $this->assertTrue($builder->isValid());

        $builder->findValidToken()?->use();

        $this->assertFalse(TokenBuilder::setUniqueId($sent)->setType('sms-verification')->isValid());
    }

    public function testAOneTimeLinkExpiresWithTime() : void
    {
        $token = TokenBuilder::setExpireDate(now()->addMinutes(5))->setData(['url' => '/reset'])->build();

        $this->assertTrue(TokenBuilder::setUniqueId($token->token)->isValid());

        $this->travelTo(now()->addMinutes(6));

        $this->assertFalse(TokenBuilder::setUniqueId($token->token)->isValid());
        $this->assertNotNull(TokenBuilder::setUniqueId($token->token)->findToken());
    }

    public function testATokenOfAUserIsBuiltAndFoundThroughThatUser() : void
    {
        $user = $this->createUser();

        // "second example: using a main model"
        $token = $user->temporaryTokenBuilder()->setUsageLimit(1)->build();

        $this->assertTrue($user->is($token->tokenable));
        $this->assertSame(1, $user->temporaryTokens()->count());

        $found = $user->temporaryTokenBuilder()->setUniqueId($token->token)->findValidToken();

        $this->assertTrue($token->is($found));
    }

    public function testAUserIsOnlySentANewPinWhenTheOneTheyHaveIsNoLongerValid() : void
    {
        $user = $this->createUser();

        // "Forgot password": no pin was sent yet, so one is built.
        $this->assertFalse($user->hasValidTemporaryToken('reset-password'));

        $pin = $user->temporaryTokenBuilder()
            ->setType('reset-password')
            ->setUsageLimit(1)
            ->setExpireDate(now()->addMinutes(5))
            ->build(6);

        // The user asks again while the pin they were sent is still valid.
        $this->assertTrue($user->hasValidTemporaryToken('reset-password'));
        $this->assertSame([$pin->getKey()], $user->validTemporaryTokens('reset-password')->modelKeys());

        // A pin of another type, or of another user, is none of this flow's business.
        $this->assertFalse($user->hasValidTemporaryToken('sms-verification'));
        $this->assertFalse($this->createUser('Someone else')->hasValidTemporaryToken('reset-password'));

        // The pin is used to reset the password, and a new one may be sent again.
        $user->temporaryTokenBuilder()->setType('reset-password')->setUniqueId($pin->token)->findValidToken()?->use();

        $this->assertFalse($user->hasValidTemporaryToken('reset-password'));
    }

    public function testTheDataOfATokenSurvivesTheRoundTrip() : void
    {
        $data = ['mobile' => '9373620353', 'name' => 'John Doe'];

        $token = TokenBuilder::setData($data)->build();

        $found = TokenBuilder::setUniqueId($token->token)->findToken();

        $this->assertSame($data, $found?->data);
        $this->assertSame('9373620353', $found->data['mobile']);
    }

    public function testTwoChainsOfTheFacadeDoNotShareTheirSettings() : void
    {
        $limited = TokenBuilder::setUsageLimit(1)->build();
        $endless = TokenBuilder::build();

        $this->assertSame(1, $limited->max_usage_limit);

        // The builder behind the facade used to be kept, so the second token
        // was built with the usage limit of the first one.
        $this->assertSame(0, $endless->max_usage_limit);
    }

    public function testEveryTokenThatIsBuiltIsStored() : void
    {
        TokenBuilder::build();
        TokenBuilder::build();

        $this->assertSame(2, Token::query()->count());
        $this->assertSame(2, Token::query()->valid()->count());
    }

    public function testAGivenTokenIsKeptAsItIs() : void
    {
        $token = TokenBuilder::setUniqueId('jgaZ1z9')->build();

        $this->assertSame('jgaZ1z9', $token->token);
        $this->assertTrue($token->is(TokenBuilder::setUniqueId('jgaZ1z9')->findToken()));
    }

    public function testTheMigrationOfThePackageCreatesTheTableItNeeds() : void
    {
        $columns = ['id', 'token', 'expired_at', 'usage_count', 'max_usage_limit', 'data', 'type',
            'tokenable_id', 'tokenable_type', 'created_at', 'updated_at', 'deleted_at'];

        foreach ($columns as $column) {
            $this->assertTrue(
                $this->app->make('db')->getSchemaBuilder()->hasColumn('tokens', $column),
                "The tokens table has no {$column} column."
            );
        }
    }
}
