<?php

namespace Tests\Unit;

use Holiq\ActionData\Foundation\DataTransferObject;

// Simple test DTO
readonly class SimpleTestUserData extends DataTransferObject
{
    public function __construct(
        public string $name,
        public string $email,
        public int $age,
    ) {
    }
}

it('can validate DTO with custom validator', function () {
    $user = new SimpleTestUserData('John Doe', 'john@example.com', 25);

    // Valid case
    $result = $user->validate(
        fn (SimpleTestUserData $data) => str_contains($data->email, '@') && $data->age >= 18,
        'User must have valid email and be adult'
    );

    expect($result)->toBe($user);
});

it('throws exception when validation fails', function () {
    $user = new SimpleTestUserData('John Doe', 'invalid-email', 16);

    $user->validate(
        fn (SimpleTestUserData $data) => str_contains($data->email, '@') && $data->age >= 18,
        'User must have valid email and be adult'
    );
})->throws(\InvalidArgumentException::class, 'User must have valid email and be adult');

it('can chain validation calls', function () {
    $user = new SimpleTestUserData('John Doe', 'john@example.com', 25);

    $result = $user
        ->validate(fn ($data) => ! empty($data->name), 'Name is required')
        ->validate(fn ($data) => str_contains($data->email, '@'), 'Valid email required')
        ->validate(fn ($data) => $data->age >= 18, 'Must be adult');

    expect($result)->toBe($user);
});
