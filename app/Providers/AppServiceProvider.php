<?php

namespace App\Providers;

use App\Models\Articles\RawArticle;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $count_raw_article = RawArticle::where('sent_status', 0)->count();
        $count_sent_lotaya = RawArticle::where('sent_status', 1)->count();
        $count_reject_article = RawArticle::where('sent_status', '>', 1)->count();
        view()->share('count_raw_article', $count_raw_article);
        view()->share('count_sent_lotaya', $count_sent_lotaya);
        view()->share('count_reject_article', $count_reject_article);
    }
}
