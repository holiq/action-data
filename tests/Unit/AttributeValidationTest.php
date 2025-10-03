<?php

namespace Tests\Unit;

use Holiq\ActionData\Attributes\Validation\Email;
use Holiq\ActionData\Attributes\Validation\Length;
use Holiq\ActionData\Attributes\Validation\Required;
use Holiq\ActionData\Foundation\DataTransferObject;

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

it('can validate DTO using attributes', function () {
    $user = new AttributeTestUserData('John Doe', 'john@example.com', 25);

    // Should pass validation
    $result = $user->validateAttributes();
    expect($result)->toBe($user);
});

it('throws exception when attribute validation fails', function () {
    $user = new AttributeTestUserData('', 'invalid-email', 25);

    $user->validateAttributes();
})->throws(\InvalidArgumentException::class);

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
        ->toThrow(\InvalidArgumentException::class);
});

it('validates email format', function () {
    $user = new AttributeTestUserData('John Doe', 'not-an-email', 25);

    expect(fn () => $user->validateAttributes())
        ->toThrow(\InvalidArgumentException::class);
});
