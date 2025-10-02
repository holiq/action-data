# Laravel Action Data

[![Latest Version on Packagist](https://img.shields.io/packagist/v/holiq/action-data.svg?style=flat-square)](https://packagist.org/packages/holiq/action-data)
[![Total Downloads](https://img.shields.io/packagist/dt/holiq/action-data.svg?style=flat-square)](https://packagist.org/packages/holiq/action-data)
[![License](https://img.shields.io/packagist/l/holiq/action-data.svg?style=flat-square)](https://packagist.org/packages/holiq/action-data)

A Laravel package that provides an elegant way to generate and use Actions and Data Transfer Objects (DTOs) in your Laravel projects. This package promotes clean architecture by separating business logic into reusable Action classes and ensuring type-safe data handling with DTOs.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Generating Actions](#generating-actions)
  - [Generating DTOs](#generating-dtos)
  - [Advanced DTO Features](#advanced-dto-features)
  - [Real-world Examples](#real-world-examples)
- [API Reference](#api-reference)
- [Testing](#testing)

## Features

- 🚀 **Simple Command Generation**: Generate Actions and DTOs with simple Artisan commands
- 🔒 **Type Safety**: Built with PHP 8.2+ readonly classes for immutable data structures
- 🏗️ **Clean Architecture**: Promotes separation of concerns and clean code practices
- 🔄 **Automatic Data Mapping**: Seamless conversion between arrays, Form Requests, and Models
- 📁 **Customizable Paths**: Configure custom paths for Actions and DTOs
- 🧪 **Well Tested**: Comprehensive test suite ensuring reliability
- 📖 **Rich Documentation**: Extensive documentation and examples

## Requirements

- PHP 8.2 or higher
- Laravel 11.0 or higher

## Installation

You can install the package via Composer:

```bash
composer require holiq/action-data
```

The package will automatically register its service provider.

Optionally, you can publish the configuration file:

```bash
php artisan vendor:publish --provider="Holiq\ActionData\ActionDataServiceProvider" --tag="config"
```

## Configuration

After publishing the configuration file, you can customize the paths where Actions and DTOs are generated:

```php
// config/action-data.php
return [
    'action_path' => 'app/Actions',
    'data_path' => 'app/DataTransferObjects',
];
```

## Usage

### Generating Actions

Create a new Action class:

```bash
php artisan make:action StoreUserAction
```

Create an Action in a subdirectory:

```bash
php artisan make:action User/StoreUserAction
```

Force overwrite an existing Action:

```bash
php artisan make:action StoreUserAction --force
```

This generates a class like:

```php
<?php

namespace App\Actions;

use Holiq\ActionData\Foundation\Action;

readonly class StoreUserAction extends Action
{
    public function execute(): mixed
    {
        // Your business logic here
    }
}
```

### Generating DTOs

Create a new Data Transfer Object:

```bash
php artisan make:dto CreateUserData
```

Create a DTO in a subdirectory:

```bash
php artisan make:dto User/CreateUserData
```

Force overwrite an existing DTO:

```bash
php artisan make:dto CreateUserData --force
```

This generates a class like:

```php
<?php

namespace App\DataTransferObjects;

use Holiq\ActionData\Foundation\DataTransferObject;

readonly class CreateUserData extends DataTransferObject
{
    final public function __construct(
        // Define your properties here
    ) {}
}
```

### Advanced DTO Features

#### Data Resolution

DTOs can automatically resolve data from various sources:

```php
// From array
$userData = CreateUserData::resolve([
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john@example.com'
]);

// From Form Request
$userData = CreateUserData::resolveFrom($request);

// From Eloquent Model
$userData = CreateUserData::resolveFrom($user);
```

#### Array Conversion

Convert DTOs to arrays with automatic snake_case conversion:

```php
readonly class CreateUserData extends DataTransferObject
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
    ) {}
}

$data = new CreateUserData('John', 'Doe', 'john@example.com');
$array = $data->toArray();
// Result: ['first_name' => 'John', 'last_name' => 'Doe', 'email' => 'john@example.com']
```

#### Excluding Properties

Control which properties are included in specific contexts:

```php
readonly class CreateUserData extends DataTransferObject
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public ?string $password = null,
    ) {}

    protected function toExcludedPropertiesOnCreate(): array
    {
        return ['password'];
    }

    protected function toExcludedPropertiesOnUpdate(): array
    {
        return ['email'];
    }
}
```

### Real-world Examples

#### Complete User Management Example

```php
// Data Transfer Object
namespace App\DataTransferObjects;

use Holiq\ActionData\Foundation\DataTransferObject;

readonly class CreateUserData extends DataTransferObject
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $password,
        public ?string $phone = null,
    ) {}
}

// Action Class
namespace App\Actions\User;

use App\DataTransferObjects\CreateUserData;
use App\Models\User;
use Holiq\ActionData\Foundation\Action;
use Illuminate\Support\Facades\Hash;

readonly class CreateUserAction extends Action
{
    public function execute(CreateUserData $data): User
    {
        return User::create([
            'first_name' => $data->firstName,
            'last_name' => $data->lastName,
            'email' => $data->email,
            'password' => Hash::make($data->password),
            'phone' => $data->phone,
        ]);
    }
}

// Form Request
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
        ];
    }
}

// Controller
namespace App\Http\Controllers;

use App\Actions\User\CreateUserAction;
use App\DataTransferObjects\CreateUserData;
use App\Http\Requests\CreateUserRequest;
use CuyZ\Valinor\Mapper\MappingError;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * @throws MappingError
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        $user = CreateUserAction::resolve()->execute(
            CreateUserData::resolve($request->validated())
        );

        return response()->json([
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }
}
```

#### Advanced Example with Service Dependencies

```php
// Action with Dependencies
namespace App\Actions\User;

use App\DataTransferObjects\CreateUserData;
use App\Models\User;
use App\Services\EmailService;
use App\Services\UserService;
use Holiq\ActionData\Foundation\Action;

readonly class CreateUserWithNotificationAction extends Action
{
    public function __construct(
        private UserService $userService,
        private EmailService $emailService,
    ) {}

    public function execute(CreateUserData $data): User
    {
        $user = $this->userService->create($data);

        $this->emailService->sendWelcomeEmail($user);

        return $user;
    }
}

// Usage in Controller
$user = CreateUserWithNotificationAction::resolve()->execute($userData);
```

## API Reference

### Action Class Methods

#### `resolve(array $parameters = []): static`

Resolves an Action instance from Laravel's container with optional parameters.

### DataTransferObject Class Methods

#### `resolve(array $data): static`

Creates a DTO instance from an array with automatic key transformation.

#### `resolveFrom(FormRequest|Model|array $abstract): static`

Creates a DTO instance from various data sources.

#### `toArray(): array`

Converts the DTO to an array with snake_case keys.

#### `clone(): static`

Creates a clone of the DTO (from Spatie\Cloneable).

#### `tap(callable $callback): static`

Executes a callback and returns the DTO instance.

#### `dd(): never`

Dumps the DTO data and dies (useful for debugging).

### Configuration Options

- `action_path`: Directory where Action classes are generated (default: `app/Actions`)
- `data_path`: Directory where DTO classes are generated (default: `app/DataTransferObjects`)

## Testing

Run the test suite:

```bash
composer test
```

Run static analysis:

```bash
composer analyse
```

Run code formatting:

```bash
composer format
```
