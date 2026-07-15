<?php

namespace App\Providers;

use App\Auth\CipherSweetEloquentUserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Activity::creating(function (Activity $activity) {
            $activity->properties = $activity->properties->merge([
                'ip' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        });

        Vite::prefetch(concurrency: 3);

        Auth::provider('ciphersweet-eloquent', function ($app, array $config) {
            return new CipherSweetEloquentUserProvider($app['hash'], $config['model']);
        });

        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }
    }
}
