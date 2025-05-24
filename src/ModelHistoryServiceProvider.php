<?php

declare(strict_types=1);

namespace Patoui\ModelHistory;

use Patoui\ModelHistory\Contracts\ModelHistoryRepositoryContract;
use Patoui\ModelHistory\Repository\EloquentModelHistoryRepository;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ModelHistoryServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('model-history')
            ->hasConfigFile('model-history')
            ->hasMigration('create_changes_table');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(ModelHistoryRepositoryContract::class, EloquentModelHistoryRepository::class);
    }
}
