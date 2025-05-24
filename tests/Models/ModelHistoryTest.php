<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Patoui\ModelHistory\Models\ModelHistory;
use Patoui\ModelHistory\Tests\User;

test('it can be instantiated', function () {
    $change = new ModelHistory;

    expect($change)->toBeInstanceOf(ModelHistory::class);
});

test('it has correct fillable attributes', function () {
    $change = new ModelHistory;

    $expected = [
        'model_type',
        'model_id',
        'old',
        'new',
        'auth_id',
    ];

    expect($change->getFillable())->toEqual($expected);
});

test('it casts json fields to arrays', function () {
    $change = new ModelHistory;

    $expectedCasts = [
        'old' => 'array',
        'new' => 'array',
    ];

    foreach ($expectedCasts as $field => $cast) {
        expect($change->getCasts()[$field])->toEqual($cast);
    }
});

test('it can create a change record', function () {
    $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

    $changeData = [
        'model_type' => 'App\\Models\\User',
        'model_id' => '1',
        'old' => ['name' => 'Jane Doe'],
        'new' => ['name' => 'John Doe'],
        'auth_id' => 0,
    ];

    $change = ModelHistory::create($changeData);

    expect($change)
        ->toBeInstanceOf(ModelHistory::class)
        ->and($change->model_type)->toEqual($changeData['model_type'])
        ->and($change->model_id)->toEqual($changeData['model_id'])
        ->and($change->old)->toEqual($changeData['old'])
        ->and($change->new)->toEqual($changeData['new'])
        ->and($change->auth_id)->toEqual($changeData['auth_id']);
});

test('it has a model relationship', function () {
    $change = new ModelHistory;

    expect($change->model())->toBeInstanceOf(MorphTo::class);
});

test('it can resolve the model relationship', function () {
    $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

    // Create a test user
    $user = User::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    // Create a change record that references the user
    $change = ModelHistory::create([
        'model_type' => User::class,
        'model_id' => $user->id,
        'old' => ['name' => 'Jane Doe'],
        'new' => ['name' => 'John Doe'],
        'auth_id' => 0,
    ]);

    // Assert the relationship can be resolved
    $resolvedModel = $change->model;

    expect($resolvedModel)
        ->toBeInstanceOf(User::class)
        ->and($resolvedModel->id)->toEqual($user->id)
        ->and($resolvedModel->name)->toEqual('John Doe')
        ->and($resolvedModel->email)->toEqual('john@example.com');
});
