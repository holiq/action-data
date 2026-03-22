<?php

declare(strict_types=1);

namespace Tests\Unit;

use Holiq\ActionData\Foundation\DataTransferObject;

// ---------------------------------------------------------------------------
// Shared test DTOs
// ---------------------------------------------------------------------------

readonly class WithMethodUserDto extends DataTransferObject
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public ?string $password = null,
        public ?string $avatar = null,
    ) {
    }
}

readonly class WithMethodAddressDto extends DataTransferObject
{
    public function __construct(
        public string $street,
        public string $city,
        public string $country,
    ) {
    }
}

readonly class WithMethodProfileDto extends DataTransferObject
{
    public function __construct(
        public string $name,
        public WithMethodAddressDto $address,
        public ?string $bio = null,
    ) {
    }
}

// ---------------------------------------------------------------------------
// with() — named argument API
// ---------------------------------------------------------------------------

it('with() returns a new instance, not the same object', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com');
    $updated = $original->with(firstName: 'Jane');

    expect($updated)->not->toBe($original);
});

it('with() overrides a single property', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com');
    $updated = $original->with(email: 'jane@example.com');

    expect($updated->email)->toBe('jane@example.com')
        ->and($updated->firstName)->toBe('John')
        ->and($updated->lastName)->toBe('Doe');
});

it('with() overrides multiple properties at once', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com');
    $updated = $original->with(firstName: 'Jane', email: 'jane@example.com');

    expect($updated->firstName)->toBe('Jane')
        ->and($updated->email)->toBe('jane@example.com')
        ->and($updated->lastName)->toBe('Doe');
});

it('with() does not mutate the original instance', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com');
    $original->with(firstName: 'Jane', email: 'jane@example.com');

    expect($original->firstName)->toBe('John')
        ->and($original->email)->toBe('john@example.com');
});

it('with() can set a nullable property to a concrete value', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com', null);
    $updated = $original->with(password: 'secret123');

    expect($updated->password)->toBe('secret123');
});

it('with() can set a nullable property back to null', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com', 'secret123');
    $updated = $original->with(password: null);

    expect($updated->password)->toBeNull();
});

it('with() can be chained multiple times', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com');

    $result = $original
        ->with(firstName: 'Jane')
        ->with(lastName: 'Smith')
        ->with(email: 'jane.smith@example.com');

    expect($result->firstName)->toBe('Jane')
        ->and($result->lastName)->toBe('Smith')
        ->and($result->email)->toBe('jane.smith@example.com');
});

it('with() preserves untouched properties through a chain', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com', 'pass', 'avatar.png');

    $result = $original->with(firstName: 'Jane');

    expect($result->lastName)->toBe('Doe')
        ->and($result->email)->toBe('john@example.com')
        ->and($result->password)->toBe('pass')
        ->and($result->avatar)->toBe('avatar.png');
});

it('with() throws InvalidArgumentException for an unknown property', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com');

    $original->with(nonExistent: 'value');
})->throws(
    \InvalidArgumentException::class,
    "Property 'nonExistent' does not exist on",
);

it('with() throws on the first unknown property and does not clone', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com');

    expect(fn () => $original->with(ghost: 'x'))
        ->toThrow(\InvalidArgumentException::class);

    // The original must be untouched
    expect($original->firstName)->toBe('John');
});

it('with() works with a nested DTO property', function () {
    $address = new WithMethodAddressDto('123 Main St', 'Anytown', 'USA');
    $original = new WithMethodProfileDto('Alice', $address, 'Developer');

    $newAddress = new WithMethodAddressDto('456 Oak Ave', 'Springfield', 'USA');
    $updated = $original->with(address: $newAddress);

    expect($updated->address->street)->toBe('456 Oak Ave')
        ->and($updated->name)->toBe('Alice')
        ->and($updated->bio)->toBe('Developer');
});

it('with() called with no arguments returns an equal but distinct instance', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com');
    $copy = $original->with();

    expect($copy)->not->toBe($original)
        ->and($copy->firstName)->toBe('John')
        ->and($copy->email)->toBe('john@example.com');
});

// ---------------------------------------------------------------------------
// withArray() — associative array API
// ---------------------------------------------------------------------------

it('withArray() overrides a property using a snake_case key', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com');
    $updated = $original->withArray(['first_name' => 'Jane']);

    expect($updated->firstName)->toBe('Jane')
        ->and($updated->lastName)->toBe('Doe');
});

it('withArray() overrides a property using a camelCase key', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com');
    $updated = $original->withArray(['firstName' => 'Jane']);

    expect($updated->firstName)->toBe('Jane');
});

it('withArray() accepts a mix of snake_case and camelCase keys', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com');
    $updated = $original->withArray([
        'first_name' => 'Jane',
        'lastName' => 'Smith',
    ]);

    expect($updated->firstName)->toBe('Jane')
        ->and($updated->lastName)->toBe('Smith')
        ->and($updated->email)->toBe('john@example.com');
});

it('withArray() overrides multiple properties at once', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com');
    $updated = $original->withArray([
        'first_name' => 'Jane',
        'email' => 'jane@example.com',
        'password' => 'newpass',
    ]);

    expect($updated->firstName)->toBe('Jane')
        ->and($updated->email)->toBe('jane@example.com')
        ->and($updated->password)->toBe('newpass');
});

it('withArray() does not mutate the original instance', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com');
    $original->withArray(['first_name' => 'Jane']);

    expect($original->firstName)->toBe('John');
});

it('withArray() returns a new instance', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com');
    $updated = $original->withArray(['first_name' => 'Jane']);

    expect($updated)->not->toBe($original);
});

it('withArray() can set a nullable property to null', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com', 'secret');
    $updated = $original->withArray(['password' => null]);

    expect($updated->password)->toBeNull();
});

it('withArray() can be chained', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com');

    $result = $original
        ->withArray(['first_name' => 'Jane'])
        ->withArray(['last_name' => 'Smith']);

    expect($result->firstName)->toBe('Jane')
        ->and($result->lastName)->toBe('Smith');
});

it('withArray() can be chained with with()', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com');

    $result = $original
        ->withArray(['first_name' => 'Jane'])
        ->with(email: 'jane@example.com');

    expect($result->firstName)->toBe('Jane')
        ->and($result->email)->toBe('jane@example.com');
});

it('withArray() throws InvalidArgumentException for an unknown snake_case key', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com');

    $original->withArray(['unknown_field' => 'value']);
})->throws(\InvalidArgumentException::class);

it('withArray() throws InvalidArgumentException for an unknown camelCase key', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com');

    $original->withArray(['unknownField' => 'value']);
})->throws(\InvalidArgumentException::class);

it('withArray() called with an empty array returns an equal but distinct instance', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com');
    $copy = $original->withArray([]);

    expect($copy)->not->toBe($original)
        ->and($copy->firstName)->toBe('John')
        ->and($copy->email)->toBe('john@example.com');
});

// ---------------------------------------------------------------------------
// without() — clear nullable properties
// ---------------------------------------------------------------------------

it('without() sets a single nullable property to null', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com', 'secret', 'avatar.png');
    $updated = $original->without('password');

    expect($updated->password)->toBeNull()
        ->and($updated->avatar)->toBe('avatar.png'); // untouched
});

it('without() sets multiple nullable properties to null at once', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com', 'secret', 'avatar.png');
    $updated = $original->without('password', 'avatar');

    expect($updated->password)->toBeNull()
        ->and($updated->avatar)->toBeNull();
});

it('without() does not mutate the original instance', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com', 'secret');
    $original->without('password');

    expect($original->password)->toBe('secret');
});

it('without() returns a new instance', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com', 'secret');
    $updated = $original->without('password');

    expect($updated)->not->toBe($original);
});

it('without() preserves all other properties', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com', 'secret', 'avatar.png');
    $updated = $original->without('password');

    expect($updated->firstName)->toBe('John')
        ->and($updated->lastName)->toBe('Doe')
        ->and($updated->email)->toBe('john@example.com')
        ->and($updated->avatar)->toBe('avatar.png');
});

it('without() can be chained', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com', 'secret', 'avatar.png');

    $updated = $original
        ->without('password')
        ->without('avatar');

    expect($updated->password)->toBeNull()
        ->and($updated->avatar)->toBeNull();
});

it('without() can be chained with with()', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com', 'secret', 'avatar.png');

    $result = $original
        ->without('password')
        ->with(firstName: 'Jane');

    expect($result->password)->toBeNull()
        ->and($result->firstName)->toBe('Jane');
});

it('without() can be chained with withArray()', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com', 'secret', 'avatar.png');

    $result = $original
        ->without('avatar')
        ->withArray(['first_name' => 'Jane']);

    expect($result->avatar)->toBeNull()
        ->and($result->firstName)->toBe('Jane');
});

it('without() on an already-null property keeps it null', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com', null);
    $updated = $original->without('password');

    expect($updated->password)->toBeNull();
});

it('without() throws InvalidArgumentException for an unknown property', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com');

    $original->without('nonExistent');
})->throws(
    \InvalidArgumentException::class,
    "Property 'nonExistent' does not exist on",
);

it('without() throws InvalidArgumentException when property is not nullable', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com');

    // 'firstName' is a non-nullable string — must not be clearable
    $original->without('firstName');
})->throws(
    \InvalidArgumentException::class,
    'is not nullable and cannot be cleared with without()',
);

it('without() throws on the first non-nullable property and does not clone', function () {
    $original = new WithMethodUserDto('John', 'Doe', 'john@example.com', 'secret');

    expect(fn () => $original->without('email')) // non-nullable
        ->toThrow(\InvalidArgumentException::class);

    // The original must be untouched
    expect($original->email)->toBe('john@example.com');
});

it('without() works with a nullable nested DTO property', function () {
    $address = new WithMethodAddressDto('123 Main St', 'Anytown', 'USA');
    $original = new WithMethodProfileDto('Alice', $address, 'Developer');

    $updated = $original->without('bio');

    expect($updated->bio)->toBeNull()
        ->and($updated->name)->toBe('Alice')
        ->and($updated->address->street)->toBe('123 Main St');
});
