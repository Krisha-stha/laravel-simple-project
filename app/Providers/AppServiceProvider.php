<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Eloquent\BookRepository;

class AppServiceProvider extends ServiceProvider
{

  public function register(): void
  {
    $this->app->bind(
    BookRepositoryInterface::class,
    BookRepository::class
    );
  }

  public function boot(): void
  {
    //
  }
}
