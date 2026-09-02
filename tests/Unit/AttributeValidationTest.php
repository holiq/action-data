<?php

namespace Tests\Unit;

use Holiq\ActionData\Attributes\Validation\Boolean;
use Holiq\ActionData\Attributes\Validation\Confirmed;
use Holiq\ActionData\Attributes\Validation\Date;
use Holiq\ActionData\Attributes\Validation\Email;
use Holiq\ActionData\Attributes\Validation\In;
use Holiq\ActionData\Attributes\Validation\Length;
use Holiq\ActionData\Attributes\Validation\Pattern;
use Holiq\ActionData\Attributes\Validation\Range;
use Holiq\ActionData\Attributes\Validation\Required;
use Holiq\ActionData\Attributes\Validation\Url;
use Holiq\ActionData\Attributes\Validation\Uuid;
use Holiq\ActionData\Foundation\DataTransferObject;
use Illuminate\Validation\ValidationException;

// Test DTO with attributes
readonly class AttributeTestUserData extends DataTransferObject
{
    public function __construct(
        #[Required]
        #[Length(min: 2, max: 50)]
        public string $name,

        #[Required]
        #[Email]
        public string $email,

        public int $age,
    ) {
    }
}

readonly class NullableAttributeTestData extends DataTransferObject
{
    public function __construct(
        #[Email]
        public ?string $email = null,

        #[Length(min: 2, max: 50)]
        public ?string $name = null,

        #[Range(min: 1, max: 120)]
        public int | float | null $age = null,

        #[Pattern(regex: '/^[A-Z]+$/')]
        public ?string $code = null,
    ) {
    }
}

readonly class ExtendedAttributeTestData extends DataTransferObject
{
    public function __construct(
        #[Url]
        public string $website,

        #[In(values: ['draft', 'published'])]
        public string $status,

        #[Date(format: 'Y-m-d')]
        public string $publishedAt,

        #[Uuid]
        public string $requestId,

        #[Boolean]
        public bool $active,

        #[Confirmed]
        public string $password,

        public string $passwordConfirmation,
    ) {
    }
}

readonly class CamelCaseAttributeTestData extends DataTransferObject
{
    public function __construct(
        #[Required]
        public string $firstName,

        #[Email]
        public string $emailAddress,
    ) {
    }
}

it('can validate DTO using attributes', function () {
    $user = new AttributeTestUserData('John Doe', 'john@example.com', 25);

    // Should pass validation
    $result = $user->validateAttributes();
    expect($result)->toBe($user);
});

it('allows null for non-required validation attributes', function () {
    $data = new NullableAttributeTestData();

    expect($data->validateAttributes())->toBe($data);
});

it('still validates non-null values on nullable fields', function () {
    $data = new NullableAttributeTestData(
        email: 'invalid-email',
        name: 'J',
        age: 0,
        code: 'lowercase',
    );

    expect(fn () => $data->validateAttributes())
        ->toThrow(ValidationException::class);
});

it('supports the extended validation attributes', function () {
    $data = new ExtendedAttributeTestData(
        website: 'https://example.com',
        status: 'published',
        publishedAt: '2026-09-01',
        requestId: '550e8400-e29b-41d4-a716-446655440000',
        active: true,
        password: 'secret',
        passwordConfirmation: 'secret',
    );

    expect($data->validateAttributes())->toBe($data);
});

it('rejects invalid values for the extended validation attributes', function () {
    $data = new ExtendedAttributeTestData(
        website: 'not-a-url',
        status: 'unknown',
        publishedAt: 'not-a-date',
        requestId: 'not-a-uuid',
        active: true,
        password: 'secret',
        passwordConfirmation: 'different',
    );

    expect(fn () => $data->validateAttributes())
        ->toThrow(ValidationException::class);
});

it('uses a custom Length message with bounds', function () {
    $attribute = new Length(
        min: 2,
        max: 10,
        message: 'The :property value must be between :min and :max.',
    );

    expect($attribute->getErrorMessage('name'))
        ->toBe('The name value must be between 2 and 10.');
});

it('throws ValidationException when attribute validation fails', function () {
    $user = new AttributeTestUserData('', 'invalid-email', 25);

    expect(fn () => $user->validateAttributes())
        ->toThrow(ValidationException::class);
});

it('ValidationException carries structured per-field errors', function () {
    $user = new AttributeTestUserData('', 'invalid-email', 25);

    try {
        $user->validateAttributes();
        $this->fail('Expected ValidationException was not thrown');
    } catch (ValidationException $e) {
        $errors = $e->errors();
        expect($errors)->toBeArray();
        expect(array_keys($errors))->toContain('name', 'email');
        expect($errors)->toHaveKey('name');
        expect($errors)->toHaveKey('email');
        expect($errors)->not->toHaveKey('age');
        expect($errors['name'][0])->toBeString();
        expect($e->getMessage())->toBeString();
    }
});

it('uses snake_case keys and field names for camelCase validation errors', function () {
    $data = new CamelCaseAttributeTestData('', 'invalid-email');

    try {
        $data->validateAttributes();
        $this->fail('Expected ValidationException was not thrown');
    } catch (ValidationException $e) {
        $errors = $e->errors();

        expect($errors)->toHaveKey('first_name');
        expect($errors)->toHaveKey('email_address');
        expect($errors)->not->toHaveKey('firstName');
        expect($errors)->not->toHaveKey('emailAddress');
        expect($errors['first_name'][0])->toBe('The first_name field is required.');
        expect($errors['email_address'][0])->toBe('The email_address field must be a valid email address.');
    }
});

it('can combine attribute and callback validation', function () {
    $user = new AttributeTestUserData('John Doe', 'john@example.com', 25); // Adult age

    $result = $user
        ->validateAttributes() // Check required, length, email
        ->validate(fn ($data) => $data->age >= 18, 'Must be adult'); // Custom validation

    expect($result)->toBe($user);
});

it('validates specific attribute rules', function () {
    // Test length validation
    $user = new AttributeTestUserData('J', 'john@example.com', 25); // Name too short

    expect(fn () => $user->validateAttributes())
        ->toThrow(ValidationException::class);
});

it('validates email format', function () {
    $user = new AttributeTestUserData('John Doe', 'not-an-email', 25);

    expect(fn () => $user->validateAttributes())
        ->toThrow(ValidationException::class);
});
