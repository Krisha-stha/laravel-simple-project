<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Eloquent\BookRepository;
use App\Models\Book;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            BookRepositoryInterface::class,
            BookRepositoryInterface::class
        );
    }

    public function boot(): void
    {
        Route::model('book', Book::class);
        
        Route::bind('book', function ($value) {
            return app(BookRepositoryInterface::class)->findOrFail($value);
        });

        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));

        Route::middleware('web')
            ->group(base_path('routes/web.php'));
    }
}