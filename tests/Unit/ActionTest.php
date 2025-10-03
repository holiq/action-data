<?php

namespace Tests\Unit;

use Holiq\ActionData\Foundation\Action;

// Test Action class for unit testing
readonly class TestAction extends Action
{
    public function __construct(
        private string $message = 'Hello World'
    ) {
    }

    public function execute(mixed ...$arguments): string
    {
        $input = $arguments[0] ?? '';

        return $this->message . ' - ' . $input;
    }
}

it('can resolve action from container', function () {
    $action = TestAction::resolve();

    expect($action)->toBeInstanceOf(TestAction::class);
});

it('can resolve action with parameters', function () {
    $action = TestAction::resolve(['message' => 'Custom Message']);

    expect($action)->toBeInstanceOf(TestAction::class);

    $result = $action->execute('test');

    expect($result)->toBe('Custom Message - test');
});

it('can execute action', function () {
    $action = new TestAction('Test Message');

    $result = $action->execute('input');

    expect($result)->toBe('Test Message - input');
});
