<?php

declare(strict_types=1);

/*
 * This file is part of Laravel Bitbucket.
 *
 * (c) Graham Campbell <hello@gjcampbell.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GrahamCampbell\Tests\Bitbucket;

use Bitbucket\Client;
use GrahamCampbell\Bitbucket\Auth\AuthenticatorFactory;
use GrahamCampbell\Bitbucket\BitbucketFactory;
use GrahamCampbell\Bitbucket\BitbucketManager;
use GrahamCampbell\Bitbucket\Cache\ConnectionFactory;
use GrahamCampbell\Bitbucket\HttpClient\BuilderFactory;
use GrahamCampbell\TestBenchCore\ServiceProviderTrait;

/**
 * This is the service provider test class.
 *
 * @author Graham Campbell <hello@gjcampbell.co.uk>
 */
class ServiceProviderTest extends AbstractTestCase
{
    use ServiceProviderTrait;

    public function testHttpClientFactoryIsInjectable(): void
    {
        $this->assertIsInjectable(BuilderFactory::class);
    }

    public function testAuthFactoryIsInjectable(): void
    {
        $this->assertIsInjectable(AuthenticatorFactory::class);
    }

    public function testCacheFactoryIsInjectable(): void
    {
        $this->assertIsInjectable(ConnectionFactory::class);
    }

    public function testBitbucketFactoryIsInjectable(): void
    {
        $this->assertIsInjectable(BitbucketFactory::class);
    }

    public function testBitbucketManagerIsInjectable(): void
    {
        $this->assertIsInjectable(BitbucketManager::class);
    }

    public function testBindings(): void
    {
        $this->assertIsInjectable(Client::class);

        $original = $this->app['bitbucket.connection'];
        $this->app['bitbucket']->reconnect();
        $new = $this->app['bitbucket.connection'];

        self::assertNotSame($original, $new);
        self::assertEquals($original, $new);
    }
}
