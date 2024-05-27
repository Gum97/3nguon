<?php

namespace Ophim\Crawler\OphimCrawler;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as SP;
use Ophim\Crawler\OphimCrawler\Console\CrawlerScheduleCommand;
use Ophim\Crawler\OphimCrawler\Option;

class OphimCrawlerServiceProvider extends SP
{
    /**
     * Get the policies defined on the provider.
     *
     * @return array
     */
    public function policies()
    {
        return [];
    }

    public function register()
    {

        config(['plugins' => array_merge(config('plugins', []), [
            'gum97/ophim-crawler' =>
            [
                'name' => 'Apii.Online Crawler',
                'package_name' => 'gum97/ophim-crawler',
                'icon' => 'la la-code-fork',
                'entries' => [
                    ['name' => 'Crawler', 'icon' => 'la la-hand-grab-o', 'url' => backpack_url('/plugin/ophim-crawler')],
                    ['name' => 'Option', 'icon' => 'la la-cog', 'url' => backpack_url('/plugin/ophim-crawler/options')],
                ],
            ]
        ])]);

        config(['logging.channels' => array_merge(config('logging.channels', []), [
            'ophim-crawler' => [
                'driver' => 'daily',
                'path' => storage_path('logs/hacoidev/ophim-crawler.log'),
                'level' => env('LOG_LEVEL', 'debug'),
                'days' => 7,
            ],
        ])]);

        config(['ophim.updaters' => array_merge(config('ophim.updaters', []), [
            [
                'name' => 'Ophim Crawler',
                'handler' => 'Ophim\Crawler\OphimCrawler\Crawler'
            ]
        ])]);
    }

    public function boot()
    {
        $this->commands([
            CrawlerScheduleCommand::class,
        ]);

        $this->app->booted(function () {
            $this->loadScheduler();
        });

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'ophim-crawler');
    }

    protected function loadScheduler()
    {
        $schedule = $this->app->make(Schedule::class);
        $schedule->command('ophim:plugins:ophim-crawler:schedule')->cron(Option::get('crawler_schedule_cron_config', '*/10 * * * *'))->withoutOverlapping();
    }
}
