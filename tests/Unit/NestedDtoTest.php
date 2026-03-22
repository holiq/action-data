<?php

use Holiq\ActionData\Foundation\DataTransferObject;

readonly class AddressDto extends DataTransferObject
{
    public function __construct(
        public readonly string $street,
        public readonly string $city,
        public readonly string $country,
    ) {
    }
}

readonly class UserDto extends DataTransferObject
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly AddressDto $address,
        /** @var AddressDto[] */
        public readonly array $previousAddresses = [],
    ) {
    }
}

readonly class CompanyDto extends DataTransferObject
{
    public function __construct(
        public readonly string $name,
        public readonly AddressDto $headquarters,
        /** @var UserDto[] */
        public readonly array $employees = [],
    ) {
    }
}

it('can resolve nested DTOs', function () {
    $data = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'address' => [
            'street' => '123 Main St',
            'city' => 'Anytown',
            'country' => 'USA',
        ],
    ];

    $user = UserDto::resolve($data);

    expect($user->name)->toBe('John Doe')
        ->and($user->email)->toBe('john@example.com')
        ->and($user->address)->toBeInstanceOf(AddressDto::class)
        ->and($user->address->street)->toBe('123 Main St')
        ->and($user->address->city)->toBe('Anytown')
        ->and($user->address->country)->toBe('USA');
});

it('can resolve arrays of nested DTOs', function () {
    $data = [
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'address' => [
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
    ];

    $user = UserDto::resolve($data);

    expect($user->previousAddresses)->toHaveCount(2)
        ->and($user->previousAddresses[0])->toBeInstanceOf(AddressDto::class)
        ->and($user->previousAddresses[0]->street)->toBe('789 Pine St')
        ->and($user->previousAddresses[1])->toBeInstanceOf(AddressDto::class)
        ->and($user->previousAddresses[1]->street)->toBe('321 Elm Dr');
});

it('can resolve deeply nested DTOs', function () {
    $data = [
        'name' => 'Acme Corp',
        'headquarters' => [
            'street' => '100 Business Blvd',
            'city' => 'Corporate City',
            'country' => 'USA',
        ],
        'employees' => [
            [
                'name' => 'Alice Johnson',
                'email' => 'alice@acme.com',
                'address' => [
                    'street' => '200 Residential Rd',
                    'city' => 'Suburbia',
                    'country' => 'USA',
                ],
            ],
            [
                'name' => 'Bob Wilson',
                'email' => 'bob@acme.com',
                'address' => [
                    'street' => '300 Downtown Ave',
                    'city' => 'Metro City',
                    'country' => 'USA',
                ],
            ],
        ],
    ];

    $company = CompanyDto::resolve($data);

    expect($company->name)->toBe('Acme Corp')
        ->and($company->headquarters)->toBeInstanceOf(AddressDto::class)
        ->and($company->headquarters->street)->toBe('100 Business Blvd')
        ->and($company->employees)->toHaveCount(2)
        ->and($company->employees[0])->toBeInstanceOf(UserDto::class)
        ->and($company->employees[0]->name)->toBe('Alice Johnson')
        ->and($company->employees[0]->address)->toBeInstanceOf(AddressDto::class)
        ->and($company->employees[0]->address->street)->toBe('200 Residential Rd');
});

it('handles missing nested data gracefully', function () {
    $data = [
        'name' => 'Simple User',
        'email' => 'simple@example.com',
        'address' => [
            'street' => '123 Simple St',
            'city' => 'Simpletown',
            'country' => 'USA',
        ],
        // previousAddresses is missing - should use default empty array
    ];

    $user = UserDto::resolve($data);

    expect($user->previousAddresses)->toBeEmpty();
});

it('can convert manually constructed DTO to array', function () {
    $user = new UserDto(
        name: 'John Doe',
        email: 'john@example.com',
        address: new AddressDto(
            street: '123 Main St',
            city: 'Anytown',
            country: 'USA',
        ),
        previousAddresses: [
            new AddressDto(
                street: '789 Pine St',
                city: 'Oldtown',
                country: 'USA',
            ),
        ]
    );

    expect($user->toArray())->toBe([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'address' => [
            'street' => '123 Main St',
            'city' => 'Anytown',
            'country' => 'USA',
        ],
        'previous_addresses' => [
            [
                'street' => '789 Pine St',
                'city' => 'Oldtown',
                'country' => 'USA',
            ],
        ],
    ]);
});

it('can convert deeply nested manually constructed DTO to array', function () {
    $company = new CompanyDto(
        name: 'Acme Corp',
        headquarters: new AddressDto(
            street: '100 Business Blvd',
            city: 'Corporate City',
            country: 'USA',
        ),
        employees: [
            new UserDto(
                name: 'Alice Johnson',
                email: 'alice@acme.com',
                address: new AddressDto(
                    street: '200 Residential Rd',
                    city: 'Suburbia',
                    country: 'USA',
                ),
                previousAddresses: [
                    new AddressDto(
                        street: '400 Old St',
                        city: 'Oldtown',
                        country: 'USA',
                    ),
                ],
            ),
            new UserDto(
                name: 'Bob Wilson',
                email: 'bob@acme.com',
                address: new AddressDto(
                    street: '300 Downtown Ave',
                    city: 'Metro City',
                    country: 'USA',
                ),
            ),
        ]
    );

    expect($company->toArray())->toBe([
        'name' => 'Acme Corp',
        'headquarters' => [
            'street' => '100 Business Blvd',
            'city' => 'Corporate City',
            'country' => 'USA',
        ],
        'employees' => [
            [
                'name' => 'Alice Johnson',
                'email' => 'alice@acme.com',
                'address' => [
                    'street' => '200 Residential Rd',
                    'city' => 'Suburbia',
                    'country' => 'USA',
                ],
                'previous_addresses' => [
                    [
                        'street' => '400 Old St',
                        'city' => 'Oldtown',
                        'country' => 'USA',
                    ],
                ],
            ],
            [
                'name' => 'Bob Wilson',
                'email' => 'bob@acme.com',
                'address' => [
                    'street' => '300 Downtown Ave',
                    'city' => 'Metro City',
                    'country' => 'USA',
                ],
                'previous_addresses' => [],
            ],
        ],
    ]);
});
