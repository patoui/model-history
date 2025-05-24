<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Patoui\ModelHistory\Models\ModelHistory;
use Patoui\ModelHistory\Repository\EloquentModelHistoryRepository;
use Patoui\ModelHistory\Tests\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    $this->repository = new EloquentModelHistoryRepository;
});

test('it can store a change record', function () {
    // Arrange
    $user = new User;
    $user->id = 1;
    $user->name = 'John Doe';
    $user->email = 'john@example.com';
    $user->exists = true;

    $modelType = User::class;
    $modelId = '1';

    // Act
    $this->repository->store($user);

    // Assert
    $this->assertDatabaseHas(ModelHistory::class, [
        'model_type' => $modelType,
        'model_id' => $modelId,
    ]);
});

test('it stores created_at with millisecond precision', function () {
    // Arrange
    $user = new User;
    $user->id = 1;
    $user->name = 'John Doe';
    $user->email = 'john@example.com';
    $user->exists = true;

    // Act
    $this->repository->store($user);

    // Assert
    $change = ModelHistory::first();
    expect($change)->not->toBeNull();

    // Verify the created_at timestamp has millisecond precision
    // The format should include 3 decimal places for milliseconds
    $createdAtString = $change->created_at->format('Y-m-d H:i:s.v');
    expect($createdAtString)->toMatch('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{3}/');
    $milliseconds = substr($createdAtString, -3);
    expect($milliseconds)
        ->toBeString()
        ->toHaveLength(3)
        ->not->toBe('000');

    expect($change->created_at)->toBeInstanceOf(CarbonImmutable::class);
});
