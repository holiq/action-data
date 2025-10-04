<?php

declare(strict_types=1);

use Holiq\ActionData\Foundation\DataTransferObject;

readonly class UserProfileDto extends DataTransferObject
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $bio = null,
        public readonly int $age = 0,
    ) {
    }

    protected static function transforms(): array
    {
        return [
            'name' => fn ($value) => trim(strtolower($value)),
            'email' => fn ($value) => trim(strtolower($value)),
            'bio' => fn ($value) => $value ? trim($value) : null,
            'age' => fn ($value) => max(0, (int) $value),
        ];
    }
}

it('applies transformations during resolve', function () {
    $data = [
        'name' => '  JOHN DOE  ',
        'email' => '  JOHN@EXAMPLE.COM  ',
        'bio' => '  Software developer with 10+ years experience  ',
        'age' => '-5', // Invalid age
    ];

    $dto = UserProfileDto::resolve($data);

    expect($dto->name)->toBe('john doe')
        ->and($dto->email)->toBe('john@example.com')
        ->and($dto->bio)->toBe('Software developer with 10+ years experience')
        ->and($dto->age)->toBe(0);
});

it('handles missing fields gracefully in transformations', function () {
    $data = [
        'name' => '  Jane Smith  ',
        'email' => '  JANE@TEST.COM  ',
        // bio is missing
        // age is missing, will use default
    ];

    $dto = UserProfileDto::resolve($data);

    expect($dto->name)->toBe('jane smith')
        ->and($dto->email)->toBe('jane@test.com')
        ->and($dto->bio)->toBeNull()
        ->and($dto->age)->toBe(0);
});

it('can use complex transformations', function () {
    readonly class ProductDto extends DataTransferObject
    {
        public function __construct(
            public readonly string $name,
            public readonly float $price,
            /** @var string[] */
            public readonly array $tags,
        ) {
        }

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

    $data = [
        'name' => '  awesome widget  ',
        'price' => '19.999',
        'tags' => ['Electronics', '', 'GADGET', null, 'Popular'],
    ];

    $dto = ProductDto::resolve($data);

    expect($dto->name)->toBe('Awesome Widget')
        ->and($dto->price)->toBe(20.0)
        ->and($dto->tags)->toBe(['electronics', 'gadget', 'popular']);
});

it('works with no transformations defined', function () {
    readonly class SimpleDto extends DataTransferObject
    {
        public function __construct(
            public readonly string $value,
        ) {
        }
    }

    $data = ['value' => 'test'];
    $dto = SimpleDto::resolve($data);

    expect($dto->value)->toBe('test');
});
