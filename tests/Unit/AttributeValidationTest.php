<?php

namespace Tests\Unit;

use Holiq\ActionData\Attributes\Validation\Email;
use Holiq\ActionData\Attributes\Validation\Length;
use Holiq\ActionData\Attributes\Validation\Pattern;
use Holiq\ActionData\Attributes\Validation\Range;
use Holiq\ActionData\Attributes\Validation\Required;
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
