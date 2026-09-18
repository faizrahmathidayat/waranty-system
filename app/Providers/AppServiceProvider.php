<?php

namespace App\Providers;

use App\Models\Article;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

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
        // Short aliases for cms_media.mediable_type, instead of full class
        // names, so the DB column stays readable. Katalog/Portofolio add
        // their own entry here in Phase 2/3 — same array, one line each.
        Relation::morphMap([
            'article' => Article::class,
        ]);
    }
}
