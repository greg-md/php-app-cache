<?php

namespace Greg\AppCache\Commands;

use Greg\Cache\CacheManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCacheCommand extends Command
{
    private $cache;

    public function __construct(CacheManager $cache)
    {
        $this->cache = $cache;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('clear:cache')
            ->setDescription('Clear application cache.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        $this->cache->clear();

        $output->writeln('Cache was successfully cleaned.');
    }
}
