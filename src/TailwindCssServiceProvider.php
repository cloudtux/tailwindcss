<?php namespace Cloudtux\TailwindCss;

use Illuminate\Support\ServiceProvider;

class TailwindCssServiceProvider extends ServiceProvider
{

    public function boot()
    {

        if ($this->app->runningInConsole()) {
            $this->commands([
                                \Cloudtux\TailwindCss\console\InstallTailwindCss::class
                            ]);
        }

    }

    public function register()
    {

    }
}
