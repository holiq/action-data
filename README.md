# Laravel Action Data

[![Latest Version on Packagist](https://img.shields.io/packagist/v/holiq/action-data.svg?style=flat-square)](https://packagist.org/packages/holiq/action-data)
[![Total Downloads](https://img.shields.io/packagist/dt/holiq/action-data.svg?style=flat-square)](https://packagist.org/packages/holiq/action-data)
[![License](https://img.shields.io/packagist/l/holiq/action-data.svg?style=flat-square)](https://packagist.org/packages/holiq/action-data)

A Laravel package that provides an elegant way to generate and use Actions and Data Transfer Objects (DTOs) in your Laravel projects. This package promotes clean architecture by separating business logic into reusable Action classes and ensuring type-safe data handling with DTOs.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Usage](#usage)
  - [Generating Actions](#generating-actions)
  - [Generating DTOs](#generating-dtos)
  - [Working with DTOs](#working-with-dtos)
  - [Validation](#validation)
  - [Nested DTOs](#nested-dtos)
  - [Data Transformations](#data-transformations)
- [Real-world Examples](#real-world-examples)
- [API Reference](#api-reference)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

## Features

- 🚀 **Simple Command Generation**: Generate Actions and DTOs with simple Artisan commands
- 🔒 **Type Safety**: Built with PHP 8.3+ readonly classes for immutable data structures
- 🏗️ **Clean Architecture**: Promotes separation of concerns and clean code practices
- 🔄 **Automatic Data Mapping**: Seamless conversion between arrays, Form Requests, and Models
- ✅ **Attribute-Based Validation**: Use PHP attributes for declarative validation rules
- 🔧 **Custom Validation**: Support for custom validation callbacks and pipelines
- 🌳 **Nested DTOs**: Automatic resolution of nested DTOs and arrays of DTOs
- 🔄 **Data Transformations**: Built-in data transformation pipeline for clean data processing
- 📁 **Customizable Paths**: Configure custom paths for Actions and DTOs
- 🧪 **Well Tested**: Comprehensive test suite ensuring reliability
- 📖 **Rich Documentation**: Extensive documentation and examples

## Requirements

- PHP 8.3 or higher
- Laravel 12.0 or 13.0

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

After publishing, you can customize the paths in `config/action-data.php`:

```php
return [
    'action_path' => 'app/Actions',
    'data_path' => 'app/DataTransferObjects',
];
```

## Quick Start

1. **Generate an Action with DTO:**

   ```bash
   php artisan make:action CreateUserAction --with-dto=CreateUserData
   ```

2. **Define your DTO with validation:**

   ```php
   readonly class CreateUserData extends DataTransferObject
   {
       public function __construct(
           #[Required, Length(min: 2, max: 50)]
           public string $name,

           #[Required, Email]
           public string $email,
       ) {}
   }
   ```

3. **Implement your Action:**

   ```php
   readonly class CreateUserAction extends Action
   {
       public function execute(CreateUserData $data): User
       {
           return User::create($data->toArray());
       }
   }
   ```

4. **Use in your controller:**

   ```php
   $userData = CreateUserData::resolve($request->validated())
       ->validateAttributes();

   $user = CreateUserAction::resolve()->execute($userData);
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

Generate Actions using the Artisan command with various options:

```bash
# Basic action
php artisan make:action StoreUserAction

# Action in subdirectory
php artisan make:action User/StoreUserAction

# Action with auto-generated DTO
php artisan make:action StoreUserAction --with-dto=StoreUserData

# Force overwrite existing files
php artisan make:action StoreUserAction --force
```

**Basic Action structure:**

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

**Action with DTO parameter:**

```php
<?php

namespace App\Actions;

use App\DataTransferObjects\StoreUserData;
use Holiq\ActionData\Foundation\Action;

readonly class StoreUserAction extends Action
{
    public function execute(StoreUserData $data): User
    {
        // Type-safe business logic with validated DTO
        return User::create($data->toArray());
    }
}
```

### Generating DTOs

Generate DTOs using the Artisan command:

```bash
# Basic DTO
php artisan make:dto CreateUserData

# DTO in subdirectory
php artisan make:dto User/CreateUserData

# Force overwrite existing files
php artisan make:dto CreateUserData --force
```

**Generated DTO structure:**

```php
<?php

namespace App\DataTransferObjects;

use Holiq\ActionData\Foundation\DataTransferObject;

readonly class CreateUserData extends DataTransferObject
{
    final public function __construct(
        // Define your properties with validation attributes
        // #[Required, Length(min: 1, max: 255)] public string $name,
        // #[Required, Email] public string $email,
    ) {}
}
```

### Working with DTOs

#### Creating and Resolving DTOs

DTOs can be created from various data sources:

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

Convert DTOs to arrays with different formatting options:

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

// Convert to snake_case (default)
$array = $data->toArray();
// Result: ['first_name' => 'John', 'last_name' => 'Doe', 'email' => 'john@example.com']

// Convert to camelCase
$camelCase = $data->toCamelCase();
// Result: ['firstName' => 'John', 'lastName' => 'Doe', 'email' => 'john@example.com']

// Convert to JSON
$json = $data->toJson();

// Convert with context-specific exclusions
$createArray = $data->toArrayForCreate();
$updateArray = $data->toArrayForUpdate();
```

#### Property Exclusion

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
        return []; // Include all properties for create
    }

    protected function toExcludedPropertiesOnUpdate(): array
    {
        return ['password']; // Exclude password from updates
    }
}
```

### Validation

Laravel Action Data provides powerful validation through PHP attributes and custom callbacks.

#### Attribute-Based Validation

Use PHP attributes for declarative validation rules:

```php
use Holiq\ActionData\Attributes\Validation\{Required, Email, Length, Range, Pattern};
use Holiq\ActionData\Foundation\DataTransferObject;

readonly class CreateUserData extends DataTransferObject
{
    public function __construct(
        #[Required, Length(min: 2, max: 50)]
        public string $name,

        #[Required, Email]
        public string $email,

        #[Required, Range(min: 18, max: 120)]
        public int $age,

        #[Pattern(regex: '/^\+?[1-9]\d{1,14}$/')]
        public ?string $phone = null,
    ) {}
}

// Validate using attributes
$user = CreateUserData::resolve($data)->validateAttributes();

// If invalid, validateAttributes() throws Illuminate\Validation\ValidationException
// with Laravel-standard per-field errors.
```

**Available validation attributes:**

- `#[Required]` - Field cannot be null, empty string, or empty array
- `#[Email]` - Validates email format
- `#[Length(min: int, max: int)]` - Validates string length
- `#[Range(min: int|float, max: int|float)]` - Validates numeric ranges
- `#[Pattern(regex: string)]` - Validates against regular expression

#### Custom Validation Callbacks

Use custom validation logic with chainable callbacks:

```php
$user = new CreateUserData('John Doe', 'john@example.com', 25);

// Single validation
$user->validate(
    fn (CreateUserData $data) => str_contains($data->email, '@'),
    'Email must contain @ symbol'
);

// Chain multiple validations
$user
    ->validate(fn ($data) => !empty($data->name), 'Name is required')
    ->validate(fn ($data) => $data->age >= 18, 'Must be adult')
    ->validateAttributes(); // Combine with attribute validation
```

#### Custom Validation Attributes

You can create your own PHP attributes by implementing `Holiq\ActionData\Contracts\Validator`:

```php
use Attribute;
use Holiq\ActionData\Contracts\Validator;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Positive implements Validator
{
    public function validate(mixed $value, string $property): bool
    {
        return is_numeric($value) && $value > 0;
    }

    public function getErrorMessage(string $property): string
    {
        return "'{$property}' must be a positive number.";
    }
}
```

### Nested DTOs

Laravel Action Data automatically resolves nested DTOs and arrays of DTOs:

#### Simple Nested DTOs

```php
readonly class AddressData extends DataTransferObject
{
    public function __construct(
        public string $street,
        public string $city,
        public string $country,
    ) {}
}

readonly class UserData extends DataTransferObject
{
    public function __construct(
        public string $name,
        public string $email,
        public AddressData $address, // Nested DTO
    ) {}
}

// Option 1: Using resolver (auto maps nested array → DTO)
$user = UserData::resolve([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'address' => [
        'street' => '123 Main St',
        'city' => 'Anytown',
        'country' => 'USA',
    ],
]);

// Option 2: Manual instantiation (must pass AddressData object)
$user = new UserData(
    name: 'John Doe',
    email: 'john@example.com',
    address: new AddressData(
        street: '123 Main St',
        city: 'Anytown',
        country: 'USA',
    ),
);

// Access nested data
echo $user->address->street; // "123 Main St"
```

#### Arrays of DTOs

```php
readonly class UserData extends DataTransferObject
{
    public function __construct(
        public string $name,
        public AddressData $currentAddress,
        /** @var AddressData[] */
        public array $previousAddresses = [], // Array of DTOs
    ) {}
}

$user = UserData::resolve([
    'name' => 'Jane Smith',
    'currentAddress' => [
        'street' => '456 Oak Ave',
        'city' => 'Springfield',
        'country' => 'USA',
    ],
    'previousAddresses' => [
        [
            'street' => '789 Pine St',
            'city' => 'Oldtown',
            'country' => 'USA',
        ],
        [
            'street' => '321 Elm Dr',
            'city' => 'Hometown',
            'country' => 'USA',
        ],
    ],
]);

// Access array of DTOs
foreach ($user->previousAddresses as $address) {
    echo $address->street; // Each item is an AddressData instance
}
```

### Data Transformations

Apply automatic data transformations during DTO resolution to clean and format your data:

```php
readonly class UserProfileData extends DataTransferObject
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $bio = null,
        public int $age = 0,
    ) {}

    /**
     * Define transformations applied during resolve()
     */
    protected static function transforms(): array
    {
        return [
            'name' => fn ($value) => trim(strtoupper($value)),
            'email' => fn ($value) => trim(strtolower($value)),
            'bio' => fn ($value) => $value ? trim($value) : null,
            'age' => fn ($value) => max(0, (int) $value), // Ensure non-negative
        ];
    }
}

$profile = UserProfileData::resolve([
    'name' => '  john doe  ',           // Becomes "JOHN DOE"
    'email' => '  JOHN@EXAMPLE.COM  ',  // Becomes "john@example.com"
    'bio' => '  Software developer  ',   // Becomes "Software developer"
    'age' => '-5',                       // Becomes 0
]);
```

**Complex transformations example:**

```php
readonly class ProductData extends DataTransferObject
{
    public function __construct(
        public string $name,
        public float $price,
        /** @var string[] */
        public array $tags,
    ) {}

    protected static function transforms(): array
    {
        return [
            'name' => fn ($value) => ucwords(trim($value)),
            'price' => fn ($value) => round((float) $value, 2),
            'tags' => fn ($value) => is_array($value)
                ? array_values(array_map('strtolower', array_filter($value)))
                : [],
        ];
    }
}

$product = ProductData::resolve([
    'name' => '  awesome widget  ',    // Becomes "Awesome Widget"
    'price' => '19.999',               // Becomes 20.0
    'tags' => ['Electronics', '', 'GADGET', null, 'Popular'], // Becomes ["electronics", "gadget", "popular"]
]);
```

### Real-world Examples

#### Complete User Management with Validation

```php
// Data Transfer Object with Validation
namespace App\DataTransferObjects;

use Holiq\ActionData\Attributes\Validation\Email;
use Holiq\ActionData\Attributes\Validation\Length;
use Holiq\ActionData\Attributes\Validation\Pattern;
use Holiq\ActionData\Attributes\Validation\Required;
use Holiq\ActionData\Foundation\DataTransferObject;

readonly class CreateUserData extends DataTransferObject
{
    public function __construct(
        #[Required]
        #[Length(min: 2, max: 50)]
        public string $firstName,

        #[Required]
        #[Length(min: 2, max: 50)]
        public string $lastName,

        #[Required]
        #[Email]
        public string $email,

        #[Required]
        #[Length(min: 8)]
        public string $password,

        #[Pattern(regex: '/^\+?[1-9]\d{1,14}$/')]
        public ?string $phone = null,
    ) {}

    /**
     * Apply data transformations
     *
     * Note: Transform keys support both camelCase and snake_case.
     * Use camelCase (firstName) or snake_case (first_name) - both work!
     */
    protected static function transforms(): array
    {
        return [
            'firstName' => fn ($value) => ucfirst(trim($value)),
            'lastName' => fn ($value) => ucfirst(trim($value)),
            'email' => fn ($value) => strtolower(trim($value)),
            'phone' => fn ($value) => $value ? preg_replace('/\D/', '', $value) : null,
        ];
    }

    protected function toExcludedPropertiesOnUpdate(): array
    {
        return ['password']; // Don't include password in updates
    }
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
        // Data is already validated and transformed
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
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function store(CreateUserRequest $request): JsonResponse
    {
        // Resolve and validate DTO
        $userData = CreateUserData::resolve($request->validated())
            ->validateAttributes();

        // Execute action with validated DTO
        $user = CreateUserAction::resolve()->execute($userData);

        return response()->json([
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }
}
```

When validation fails, Laravel will automatically convert the
`ValidationException` to a standard `422 Unprocessable Entity` response.

#### Nested DTOs Example: Order Management

```php
// Address DTO
readonly class AddressData extends DataTransferObject
{
    public function __construct(
        #[Required] public string $street,
        #[Required] public string $city,
        #[Required] public string $state,
        #[Required] public string $zipCode,
        #[Required] public string $country,
    ) {}
}

// Order Item DTO
readonly class OrderItemData extends DataTransferObject
{
    public function __construct(
        #[Required] public string $productId,
        #[Required] public int $quantity,
        #[Required] public float $price,
    ) {}

    protected static function transforms(): array
    {
        return [
            'quantity' => fn ($value) => max(1, (int) $value),
            'price' => fn ($value) => round((float) $value, 2),
        ];
    }
}

// Main Order DTO
readonly class CreateOrderData extends DataTransferObject
{
    public function __construct(
        #[Required] public string $customerId,
        #[Required] public AddressData $shippingAddress,
        #[Required] public AddressData $billingAddress,
        /** @var OrderItemData[] */
        #[Required] public array $items,
        public ?string $notes = null,
    ) {}
}

// Usage
$orderData = CreateOrderData::resolve([
    'customer_id' => '12345',
    'shipping_address' => [
        'street' => '123 Main St',
        'city' => 'Anytown',
        'state' => 'CA',
        'zip_code' => '12345',
        'country' => 'USA',
    ],
    'billing_address' => [
        'street' => '456 Oak Ave',
        'city' => 'Somewhere',
        'state' => 'NY',
        'zip_code' => '67890',
        'country' => 'USA',
    ],
    'items' => [
        [
            'product_id' => 'prod-1',
            'quantity' => 2,
            'price' => 29.99,
        ],
        [
            'product_id' => 'prod-2',
            'quantity' => 1,
            'price' => 15.50,
        ],
    ],
    'notes' => 'Please handle with care',
]);
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

### Action Class

#### `resolve(array $parameters = []): static`

Resolves an Action instance from Laravel's container with optional parameters.

### DataTransferObject Class

#### Core Methods

**`resolve(array $data): static`**  
Creates a DTO instance from an array with automatic key transformation and data transformations.

**`resolveFrom(FormRequest|Model|array $abstract): static`**  
Creates a DTO instance from various data sources.

#### Array Conversion

**`toArray(): array`** - Converts to snake_case array  
**`toCamelCase(): array`** - Converts to camelCase array  
**`toArrayForCreate(): array`** - Excludes properties from `toExcludedPropertiesOnCreate()`  
**`toArrayForUpdate(): array`** - Excludes properties from `toExcludedPropertiesOnUpdate()`  
**`toJson(int $options = 0): string`** - Converts to JSON string
**`toCamelJson(int $options = 0): string`** - Converts to camelCase JSON string

#### Validation

**`validate(callable $validator, string $message): static`**  
Validates using a custom callback.

**`validateAttributes(): static`**  
Validates using PHP attributes.

#### Property Access

**`has(string $property): bool`** - Checks if property exists  
**`get(string $property, mixed $default = null): mixed`** - Gets property value

#### Immutable Update Helpers

**`with(mixed ...$values): static`** - Returns a cloned DTO with named-argument overrides  
**`withArray(array $overrides): static`** - Returns a cloned DTO with snake_case/camelCase key overrides  
**`without(string ...$properties): static`** - Returns a cloned DTO with nullable properties set to null

#### Utility Methods

**`clone(): static`** - Creates a clone  
**`tap(callable $callback): static`** - Executes callback and returns instance  
**`dump(): static`** - Dumps data for debugging  
**`dd(): never`** - Dumps data and dies

#### Protected Methods (Override in your DTOs)

**`toExcludedPropertiesOnCreate(): array`** - Properties to exclude in create context  
**`toExcludedPropertiesOnUpdate(): array`** - Properties to exclude in update context  
**`transforms(): array`** - Data transformations for resolve()

### Artisan Commands

#### `make:action [name]`

Generates a new Action class.

**Options:**

- `--with-dto=DtoName` - Auto-generate corresponding DTO
- `--force` - Overwrite existing files

#### `make:dto [name]`

Generates a new Data Transfer Object class.

**Options:**

- `--force` - Overwrite existing files

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

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
