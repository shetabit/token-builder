<?php

namespace Shetabit\TokenBuilder\Tests\Unit;

use Illuminate\Support\ServiceProvider;
use Shetabit\TokenBuilder\Builder;
use Shetabit\TokenBuilder\Facade\TokenBuilder;
use Shetabit\TokenBuilder\Provider\TokenBuilderServiceProvider;
use Shetabit\TokenBuilder\Tests\TestCase;

final class TokenBuilderServiceProviderTest extends TestCase
{
    public function testItBindsTheBuilderToTheContainer() : void
    {
        $this->assertInstanceOf(Builder::class, $this->app->make(TokenBuilder::SERVICE_NAME));
        $this->assertInstanceOf(Builder::class, $this->app->make(Builder::class));
    }

    public function testEveryResolveAnswersWithABuilderOfItsOwn() : void
    {
        // A builder carries the token it is building, so two callers must not
        // end up sharing one.
        $this->assertNotSame(
            $this->app->make(TokenBuilder::SERVICE_NAME),
            $this->app->make(TokenBuilder::SERVICE_NAME)
        );
    }

    public function testTheFacadeResolvesABuilder() : void
    {
        $this->assertSame(TokenBuilder::SERVICE_NAME, TokenBuilder::getFacadeAccessor());
        $this->assertInstanceOf(Builder::class, TokenBuilder::getFacadeRoot());
    }

    public function testTheFacadeStartsANewBuilderForEveryChain() : void
    {
        TokenBuilder::setUsageLimit(3)->setType('login');

        // The instance a facade keeps used to hand the settings of a token over
        // to the next one that is built in the same request.
        $this->assertSame(0, TokenBuilder::getUsageLimit());
        $this->assertNull(TokenBuilder::getType());
    }

    public function testItPublishesTheMigrations() : void
    {
        $expected = [dirname(__DIR__, 2).'/database/migrations' => database_path('migrations')];

        $this->assertSame(
            $expected,
            ServiceProvider::pathsToPublish(TokenBuilderServiceProvider::class, 'migrations')
        );
        $this->assertSame(
            $expected,
            ServiceProvider::pathsToPublish(TokenBuilderServiceProvider::class, 'token-builder-migrations')
        );
    }
}
