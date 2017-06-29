<?php

namespace Greg\AppCache;

use App\Application;
use App\Console\ConsoleKernel;
use Greg\AppCache\Commands\ClearCacheCommand;
use Greg\Cache\CacheManager;
use Greg\Cache\RedisCache;
use Greg\Framework\ServiceProvider;

class CacheServiceProvider implements ServiceProvider
{
    private const CONFIG_NAME = 'cache';

    private $app;

    public function name()
    {
        return 'greg-cache';
    }

    public function boot(Application $app)
    {
        $this->app = $app;

        $app->inject(CacheManager::class, function () {
            $manager = new CacheManager();

            foreach ((array) $this->config('stores') as $name => $credentials) {
                $manager->register($name, function () use ($name, $credentials) {
                    $type = $credentials['type'] ?? null;

                    if ($type == 'redis') {
                        $redis = new \Redis();

                        $redis->connect($credentials['host'] ?? '127.0.0.1', $credentials['port'] ?? 6379);

                        return new RedisCache($redis);
                    }

                    throw new \Exception('Unsupported cache type `' . $type . '` for `' . $name . '`.');
                });
            }

            if ($defaultStore = $this->config('default_store')) {
                $manager->setDefaultStoreName($defaultStore);
            }

            return $manager;
        });
    }

    public function bootConsoleKernel(ConsoleKernel $kernel)
    {
        $kernel->addCommand(ClearCacheCommand::class);
    }

    private function config(string $name)
    {
        return $this->app()->config(self::CONFIG_NAME . '.' . $name);
    }

    private function app(): Application
    {
        return $this->app;
    }
}
