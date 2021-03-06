<?php

namespace Greg\AppCache;

use Greg\AppCache\Commands\ClearCacheCommand;
use Greg\AppInstaller\Application;
use Greg\AppInstaller\Events\ConfigAddEvent;
use Greg\AppInstaller\Events\ConfigRemoveEvent;
use Greg\Cache\CacheManager;
use Greg\Cache\RedisCache;
use Greg\Framework\Console\ConsoleKernel;
use Greg\Framework\ServiceProvider;

class CacheServiceProvider implements ServiceProvider
{
    const TYPE_REDIS = 'redis';

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

                    if ($type == self::TYPE_REDIS) {
                        $redis = new \Redis();

                        $redis->connect($credentials['host'] ?? '127.0.0.1', $credentials['port'] ?? 6379);

                        return new RedisCache($redis);
                    }

                    throw new \Exception('Unsupported cache type `' . $type . '` for `' . $name . '` strategy.');
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

    public function install(Application $app)
    {
        $app->event(new ConfigAddEvent(__DIR__ . '/../config/config.php', self::CONFIG_NAME));
    }

    public function uninstall(Application $app)
    {
        $app->event(new ConfigRemoveEvent(self::CONFIG_NAME));
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
