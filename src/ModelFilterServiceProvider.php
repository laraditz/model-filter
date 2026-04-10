<?php

declare(strict_types=1);

namespace Laraditz\ModelFilter;

use Illuminate\Support\ServiceProvider;
use Laraditz\ModelFilter\Console\MakeFilterCommand;

class ModelFilterServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([MakeFilterCommand::class]);
        }
    }
}
