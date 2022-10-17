<?php

namespace Transtrackid\ArchSupport;

use Illuminate\Support\ServiceProvider;

class ArchSupportServiceProvider extends ServiceProvider
{

    public function boot()
    {

    }

    public function register()
    {
        $this->app->singleton('ttid:arch', function ($app) {
            return new ArchSupportCommand();
        });

        $this->commands([
            'ttid:arch',
        ]);
    }
}
