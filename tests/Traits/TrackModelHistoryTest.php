<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Patoui\ModelHistory\Contracts\ModelHistoryRepositoryContract;
use Patoui\ModelHistory\Tests\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
});

afterEach(function () {
    Mockery::close();
});

test('it tracks model creation', function () {
    // Arrange
    $repositoryMock = Mockery::mock(ModelHistoryRepositoryContract::class);
    $this->app->instance(ModelHistoryRepositoryContract::class, $repositoryMock);

    $testModel = new User;
    $testModel->name = 'John Doe';
    $testModel->email = 'john@example.com';

    $repositoryMock->shouldReceive('store')
        ->once()
        ->with(Mockery::type(User::class));

    // Act
    $result = $testModel->save();

    // Assert
    expect($result)->toBeTrue();
});

test('it tracks model updates', function () {
    // Arrange
    $repositoryMock = Mockery::mock(ModelHistoryRepositoryContract::class);
    $this->app->instance(ModelHistoryRepositoryContract::class, $repositoryMock);

    // Expect creation tracking first
    $repositoryMock->shouldReceive('store')
        ->once()
        ->with(Mockery::type(User::class));

    // Create model first
    $testModel = new User;
    $testModel->name = 'John Doe';
    $testModel->email = 'john@example.com';
    $testModel->save();

    // Update the model
    $testModel->name = 'Jane Doe';

    $repositoryMock->shouldReceive('store')
        ->once()
        ->with(Mockery::type(User::class));

    // Act
    $result = $testModel->save();

    // Assert
    expect($result)->toBeTrue();
});

test('it only tracks dirty attributes on update', function () {
    // Arrange
    $repositoryMock = Mockery::mock(ModelHistoryRepositoryContract::class);
    $this->app->instance(ModelHistoryRepositoryContract::class, $repositoryMock);

    // Expect creation tracking first
    $repositoryMock->shouldReceive('store')
        ->once()
        ->with(Mockery::type(User::class));

    // Create model first
    $testModel = new User;
    $testModel->name = 'John Doe';
    $testModel->email = 'john@example.com';
    $testModel->save();

    // Update only one attribute
    $testModel->name = 'Jane Doe';
    // email remains unchanged

    $repositoryMock->shouldReceive('store')
        ->once()
        ->with(Mockery::type(User::class));

    // Act
    $result = $testModel->save();

    // Assert
    expect($result)->toBeTrue();
});

test('it returns new model history properties for dirty attributes', function () {
    // Arrange
    $testModel = new User;
    $testModel->name = 'John Doe';
    $testModel->email = 'john@example.com';
    $testModel->save();

    // Update attributes to make them dirty
    $testModel->name = 'Jane Doe';
    $testModel->email = 'jane@example.com';

    // Act
    $newProperties = $testModel->getNewModelHistoryProperties();

    // Assert
    expect($newProperties)->toHaveKeys(['name', 'email'])
        ->and($newProperties['name'])->toBe('Jane Doe')
        ->and($newProperties['email'])->toBe('jane@example.com');
});

test('it returns old model history properties for original values', function () {
    // Arrange
    $testModel = new User;
    $testModel->name = 'John Doe';
    $testModel->email = 'john@example.com';
    $testModel->save();

    // Update attributes to make them dirty
    $testModel->name = 'Jane Doe';
    $testModel->email = 'jane@example.com';

    // Act
    $oldProperties = $testModel->getOldModelHistoryProperties();

    // Assert
    expect($oldProperties)->toHaveKeys(['name', 'email'])
        ->and($oldProperties['name'])->toBe('John Doe')
        ->and($oldProperties['email'])->toBe('john@example.com');
});

test('it excludes configured properties from new model history properties', function () {
    // Arrange
    config(['model-history.exclude_properties' => ['email']]);

    $testModel = new User;
    $testModel->name = 'John Doe';
    $testModel->email = 'john@example.com';
    $testModel->save();

    // Update attributes to make them dirty
    $testModel->name = 'Jane Doe';
    $testModel->email = 'jane@example.com';

    // Act
    $newProperties = $testModel->getNewModelHistoryProperties();

    // Assert
    expect($newProperties)->toHaveKey('name')
        ->and($newProperties)->not->toHaveKey('email')
        ->and($newProperties['name'])->toBe('Jane Doe');
});

test('it excludes configured properties from old model history properties', function () {
    // Arrange
    config(['model-history.exclude_properties' => ['email']]);

    $testModel = new User;
    $testModel->name = 'John Doe';
    $testModel->email = 'john@example.com';
    $testModel->save();

    // Update attributes to make them dirty
    $testModel->name = 'Jane Doe';
    $testModel->email = 'jane@example.com';

    // Act
    $oldProperties = $testModel->getOldModelHistoryProperties();

    // Assert
    expect($oldProperties)->toHaveKey('name')
        ->and($oldProperties)->not->toHaveKey('email')
        ->and($oldProperties['name'])->toBe('John Doe');
});

test('it returns empty array for new model history properties when no dirty attributes', function () {
    // Arrange
    $testModel = new User;
    $testModel->name = 'John Doe';
    $testModel->email = 'john@example.com';
    $testModel->save();

    // Don't modify any attributes

    // Act
    $newProperties = $testModel->getNewModelHistoryProperties();

    // Assert
    expect($newProperties)->toBeEmpty();
});

test('it returns empty array for old model history properties when no dirty attributes', function () {
    // Arrange
    $testModel = new User;
    $testModel->name = 'John Doe';
    $testModel->email = 'john@example.com';
    $testModel->save();

    // Don't modify any attributes

    // Act
    $oldProperties = $testModel->getOldModelHistoryProperties();

    // Assert
    expect($oldProperties)->toBeEmpty();
});

test('it only returns old properties for attributes that are dirty', function () {
    // Arrange
    $testModel = new User;
    $testModel->name = 'John Doe';
    $testModel->email = 'john@example.com';
    $testModel->save();

    // Update only one attribute
    $testModel->name = 'Jane Doe';
    // email remains unchanged

    // Act
    $oldProperties = $testModel->getOldModelHistoryProperties();

    // Assert
    expect($oldProperties)->toHaveKey('name')
        ->and($oldProperties)->not->toHaveKey('email')
        ->and($oldProperties['name'])->toBe('John Doe');
});

test('it allows model to override excluded properties', function () {
    // Arrange
    config(['model-history.exclude_properties' => ['email']]);

    // Create a test model that overrides excluded properties
    $testModel = new class extends User
    {
        protected $table = 'users';

        protected array $modelHistoryExcludedProperties = ['name'];
    };

    $testModel->name = 'John Doe';
    $testModel->email = 'john@example.com';
    $testModel->save();

    // Update attributes to make them dirty
    $testModel->name = 'Jane Doe';
    $testModel->email = 'jane@example.com';

    // Act
    $newProperties = $testModel->getNewModelHistoryProperties();
    $oldProperties = $testModel->getOldModelHistoryProperties();

    // Assert - should exclude 'name' (model override) but include 'email' (ignoring config)
    expect($newProperties)->toHaveKey('email')
        ->and($newProperties)->not->toHaveKey('name')
        ->and($newProperties['email'])->toBe('jane@example.com')
        ->and($oldProperties)->toHaveKey('email')
        ->and($oldProperties)->not->toHaveKey('name')
        ->and($oldProperties['email'])->toBe('john@example.com');
});

test('it uses config excluded properties when model property is not set', function () {
    // Arrange
    config(['model-history.exclude_properties' => ['email']]);

    $testModel = new User;
    $testModel->name = 'John Doe';
    $testModel->email = 'john@example.com';
    $testModel->save();

    // Update attributes to make them dirty
    $testModel->name = 'Jane Doe';
    $testModel->email = 'jane@example.com';

    // Act
    $excludedProperties = $testModel->getModelHistoryExcludedProperties();

    // Assert
    expect($excludedProperties)->toBe(['email']);
});
