<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use AsyncAws\Core\AwsClientFactory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('aws', function ($app) {
            return new AwsClientFactory([
                'region'            => env('AWS_SNS_DEFAULT_REGION'),
                'accessKeyId'       => env('AWS_SNS_ACCESS_KEY_ID'),
                'accessKeySecret'   => env('AWS_SNS_SECRET_ACCESS_KEY')
            ]);
        });
    }
}
