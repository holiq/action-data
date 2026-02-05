<?php

namespace Tests\Unit;

use Holiq\ActionData\Foundation\DataTransferObject;

// Test DTO class for unit testing
readonly class TestUserData extends DataTransferObject
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public ?string $password = null,
    ) {
    }

    protected function toExcludedPropertiesOnCreate(): array
    {
        return ['password'];
    }

    protected function toExcludedPropertiesOnUpdate(): array
    {
        return ['email'];
    }
}

readonly class PersonDto extends DataTransferObject
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly int $age,
        /** @var string[] */
        public readonly array $hobbies,
        /** @var array<string, string|int|float> */
        public readonly array $address,
    ) {
    }
}

it('can resolve DTO from array', function () {
    $data = TestUserData::resolve([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'password' => 'secret123',
    ]);

    expect($data)->toBeInstanceOf(TestUserData::class)
        ->and($data->firstName)->toBe('John')
        ->and($data->lastName)->toBe('Doe')
        ->and($data->email)->toBe('john@example.com')
        ->and($data->password)->toBe('secret123');
});

it('converts DTO to array with snake_case keys', function () {
    $data = new TestUserData(
        firstName: 'John',
        lastName: 'Doe',
        email: 'john@example.com',
        password: 'secret123'
    );

    $array = $data->toArray();

    expect($array)->toBe([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'password' => 'secret123',
    ]);
});

it('can convert to array for create with exclusions', function () {
    $data = new TestUserData(
        firstName: 'John',
        lastName: 'Doe',
        email: 'john@example.com',
        password: 'secret123'
    );

    $array = $data->toArrayForCreate();

    expect($array)->toBe([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
    ]);
});

it('can convert to array for update with exclusions', function () {
    $data = new TestUserData(
        firstName: 'John',
        lastName: 'Doe',
        email: 'john@example.com',
        password: 'secret123'
    );

    $array = $data->toArrayForUpdate();

    expect($array)->toBe([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'password' => 'secret123',
    ]);
});

it('can check if property exists', function () {
    $data = new TestUserData(
        firstName: 'John',
        lastName: 'Doe',
        email: 'john@example.com'
    );

    expect($data->has('firstName'))->toBeTrue()
        ->and($data->has('nonExistent'))->toBeFalse();
});

it('can get property value with default', function () {
    $data = new TestUserData(
        firstName: 'John',
        lastName: 'Doe',
        email: 'john@example.com'
    );

    expect($data->get('firstName'))->toBe('John')
        ->and($data->get('nonExistent', 'default'))->toBe('default');
});

it('can convert to JSON', function () {
    $data = new TestUserData(
        firstName: 'John',
        lastName: 'Doe',
        email: 'john@example.com'
    );

    $json = $data->toJson();

    expect($json)->toBeJson();
});

it('can convert to camelCase JSON', function () {
    $data = new TestUserData(
        firstName: 'John',
        lastName: 'Doe',
        email: 'john@example.com'
    );

    $json = $data->toCamelJson();

    expect($json)->toBeJson();
});

it('can convert to camelCase array', function () {
    $data = new TestUserData(
        firstName: 'John',
        lastName: 'Doe',
        email: 'john@example.com'
    );

    $camelCase = $data->toCamelCase();

    expect($camelCase)->toBe([
        'firstName' => 'John',
        'lastName' => 'Doe',
        'email' => 'john@example.com',
        'password' => null,
    ]);
});

it('throws exception for unsupported data type in resolveFrom', function () {
    TestUserData::resolveFrom(new \stdClass());
})->throws(\Holiq\ActionData\Exceptions\InvalidArgumentException::class);
