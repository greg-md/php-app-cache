<?php

namespace Greg\AppImagix;

use DebugBar\StandardDebugBar;
use Greg\AppCache\CacheServiceProvider;
use Greg\AppInstaller\Application;
use Greg\Cache\CacheManager;
use Greg\Cache\CacheStrategy;
use Greg\Cache\RedisCache;
use Greg\Framework\Http\HttpKernel;
use Greg\Framework\ServiceProvider;
use Greg\Support\Dir;
use Greg\Support\Http\Response;
use PHPUnit\Framework\TestCase;

class CacheServiceProviderTest extends TestCase
{
    private $rootPath = __DIR__ . '/app';

    protected function setUp()
    {
        Dir::make($this->rootPath);

        Dir::make($this->rootPath . '/app');
        Dir::make($this->rootPath . '/build-deploy');
        Dir::make($this->rootPath . '/config');
        Dir::make($this->rootPath . '/public');
        Dir::make($this->rootPath . '/resources');
        Dir::make($this->rootPath . '/storage');
    }

    protected function tearDown()
    {
        Dir::unlink($this->rootPath);
    }

    public function testCanInstantiate()
    {
        $serviceProvider = new CacheServiceProvider();

        $this->assertInstanceOf(ServiceProvider::class, $serviceProvider);
    }

    public function testCanGetName()
    {
        $serviceProvider = new CacheServiceProvider();

        $this->assertEquals('greg-cache', $serviceProvider->name());
    }

    public function testCanBoot()
    {
        $serviceProvider = new CacheServiceProvider();

        $app = new Application([
            'cache' => [
                'default_store' => 'base',

                'stores' => [
                    'base' => [
                        'type' => \Greg\AppCache\CacheServiceProvider::TYPE_REDIS,
                        'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
                        'port' => getenv('REDIS_PORT') ?: '6379',
                    ],
                ],
            ],
        ]);

        $app->configure($this->rootPath);

        $serviceProvider->boot($app);

        /** @var CacheManager $manager */
        $manager = $app->get(CacheManager::class);

        $this->assertInstanceOf(CacheManager::class, $manager);

        $this->assertEquals($app->config('cache.default_store'), $manager->getDefaultStoreName());

        /** @var CacheStrategy $baseStore */
        $baseStore = $manager->store('base');

        $this->assertInstanceOf(RedisCache::class, $baseStore);
    }

    public function testCanInstall()
    {
        $serviceProvider = new CacheServiceProvider();

        $app = new Application();

        $app->configure($this->rootPath);

        $serviceProvider->install($app);

        $this->assertFileExists(__DIR__ . '/app/config/cache.php');
    }

    public function testCanUninstall()
    {
        $serviceProvider = new CacheServiceProvider();

        $app = new Application();

        $app->configure($this->rootPath);

        file_put_contents(__DIR__ . '/app/config/cache.php', '');

        $serviceProvider->uninstall($app);

        $this->assertFileNotExists(__DIR__ . '/app/config/debug_bar.php');
    }
}
