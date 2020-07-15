<?php

declare(strict_types=1);

/*
 * This file is part of Laravel Bitbucket.
 *
 * (c) Graham Campbell <graham@alt-three.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GrahamCampbell\Bitbucket;

use Bitbucket\Client;
use Bitbucket\HttpClient\Builder;
use GrahamCampbell\Bitbucket\Auth\AuthenticatorFactory;
use GrahamCampbell\Bitbucket\Cache\ConnectionFactory;
use Http\Client\Common\Plugin\RetryPlugin;
use Illuminate\Support\Arr;
use InvalidArgumentException;

/**
 * This is the bitbucket factory class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class BitbucketFactory
{
    /**
     * The cache lifetime of 48 hours.
     *
     * @var int
     */
    private const CACHE_LIFETIME = 172800;

    /**
     * The authenticator factory instance.
     *
     * @var \GrahamCampbell\Bitbucket\Auth\AuthenticatorFactory
     */
    protected $auth;

    /**
     * The cache factory instance.
     *
     * @var \GrahamCampbell\Bitbucket\Cache\ConnectionFactory
     */
    protected $cache;

    /**
     * Create a new bitbucket factory instance.
     *
     * @param \GrahamCampbell\Bitbucket\Auth\AuthenticatorFactory $auth
     * @param \GrahamCampbell\Bitbucket\Cache\ConnectionFactory   $cache
     *
     * @return void
     */
    public function __construct(AuthenticatorFactory $auth, ConnectionFactory $cache)
    {
        $this->auth = $auth;
        $this->cache = $cache;
    }

    /**
     * Make a new bitbucket client.
     *
     * @param string[] $config
     *
     * @throws \InvalidArgumentException
     *
     * @return \Bitbucket\Client
     */
    public function make(array $config)
    {
        $client = new Client($this->getBuilder($config));

        if (!array_key_exists('method', $config)) {
            throw new InvalidArgumentException('The bitbucket factory requires an auth method.');
        }

        if ($url = Arr::get($config, 'url')) {
            $client->setUrl($url);
        }

        if ($config['method'] === 'none') {
            return $client;
        }

        return $this->auth->make($config['method'])->with($client)->authenticate($config);
    }

    /**
     * Get the http client builder.
     *
     * @param string[] $config
     *
     * @return \Bitbucket\HttpClient\Builder
     */
    protected function getBuilder(array $config)
    {
        $builder = new Builder();

        if ($backoff = Arr::get($config, 'backoff')) {
            $builder->addPlugin(new RetryPlugin(['retries' => $backoff === true ? 2 : $backoff]));
        }

        if (is_array($cache = Arr::get($config, 'cache', false))) {
            $builder->addCache($this->cache->make($cache), ['cache_lifetime' => self::CACHE_LIFETIME]);
        }

        return $builder;
    }
}
