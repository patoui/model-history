<?php

declare(strict_types=1);

use Patoui\ModelHistory\Contracts\ModelHistoryRepositoryContract;
use Patoui\ModelHistory\ModelHistoryServiceProvider;
use Patoui\ModelHistory\Repository\EloquentModelHistoryRepository;

test('it binds change repository interface to eloquent implementation', function () {
    // Act
    $implementation = $this->app->make(ModelHistoryRepositoryContract::class);

    // Assert
    expect($implementation)->toBeInstanceOf(EloquentModelHistoryRepository::class);
});

test('it registers the service provider', function () {
    // Act
    $providers = $this->app->getLoadedProviders();

    // Assert
    expect($providers)->toHaveKey(ModelHistoryServiceProvider::class);
});

test('it can resolve change repository from container', function () {
    // Act
    $repository = app(ModelHistoryRepositoryContract::class);

    // Assert
    expect($repository)
        ->toBeInstanceOf(ModelHistoryRepositoryContract::class)
        ->toBeInstanceOf(EloquentModelHistoryRepository::class);
});
