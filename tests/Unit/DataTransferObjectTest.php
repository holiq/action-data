<?php

namespace Tests\Unit;

use CuyZ\Valinor\Mapper\MappingError;
use Holiq\ActionData\Exceptions\InvalidArgumentException;
use Holiq\ActionData\Exceptions\MappingException;
use Holiq\ActionData\Foundation\DataTransferObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

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

class TestUserFormRequest extends FormRequest
{
    public function __construct(private array $payload)
    {
        parent::__construct();
    }

    public function validated($key = null, $default = null): array
    {
        return $this->payload;
    }
}

class TestUserModel extends Model
{
    public function toArray(): array
    {
        return [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
        ];
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

it('can resolve DTO from a FormRequest', function () {
    $data = TestUserData::resolveFrom(new TestUserFormRequest([
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'email' => 'jane@example.com',
    ]));

    expect($data->firstName)->toBe('Jane')
        ->and($data->lastName)->toBe('Smith')
        ->and($data->email)->toBe('jane@example.com');
});

it('can resolve DTO from an Eloquent model', function () {
    $data = TestUserData::resolveFrom(new TestUserModel());

    expect($data->firstName)->toBe('Jane')
        ->and($data->lastName)->toBe('Smith')
        ->and($data->email)->toBe('jane@example.com');
});

it('rejects null as a DTO source', function () {
    TestUserData::resolveFrom(null);
})->throws(InvalidArgumentException::class, 'Cannot resolve');

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
})->throws(InvalidArgumentException::class);

it('wraps valinor mapping errors into mapping exception', function () {
    try {
        TestUserData::resolve([
            'first_name' => 'John',
            // Missing required fields to trigger a mapping failure
        ]);

        test()->fail('Expected MappingException to be thrown.');
    } catch (MappingException $exception) {
        expect($exception->getPrevious())->toBeInstanceOf(MappingError::class);
    }
});
