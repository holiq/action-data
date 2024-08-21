<?php

namespace Tests\Feature\Commands;

use Holiq\ActionData\Exceptions\FileAlreadyExistException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

beforeEach(closure: function () {
    (new Filesystem())
        ->deleteDirectory(directory: actionPath());
});

afterEach(closure: function () {
    (new Filesystem())
        ->deleteDirectory(directory: actionPath());
});

it(description: 'can generate new Action class')
    ->defer(function () {
        $fileName = 'StoreUserAction.php';

        expect(value: fileExists(relativeFileName: $fileName, path: actionPath()))->toBeFalse();

        Artisan::call(command: 'make:action StoreUserAction');

        expect(value: fileExists(relativeFileName: $fileName, path: actionPath()))->toBeTrue()
            ->and(
                value: Str::contains(
                    haystack: fileGet(relativeFileName: $fileName, path: actionPath()),
                    needles: ['{{ class }}', '{{ namespace }}']
                )
            )->toBeFalse();
    })
    ->group('commands');

it(description: 'can generate new Action class with separator')
    ->defer(function () {
        $fileName = '/Foo/BarAction.php';

        expect(value: fileExists(relativeFileName: $fileName, path: actionPath()))->toBeFalse();

        Artisan::call(command: 'make:action Foo/BarAction');

        expect(value: fileExists(relativeFileName: $fileName, path: actionPath()))->toBeTrue()
            ->and(
                value: Str::contains(
                    haystack: fileGet(relativeFileName: $fileName, path: actionPath()),
                    needles: ['{{ class }}', '{{ namespace }}']
                )
            )->toBeFalse();
    })
    ->group('commands');

it(description: 'can force generate exists Action class')
    ->defer(function () {
        $fileName = '/StoreUserAction.php';

        expect(value: fileExists(relativeFileName: $fileName, path: actionPath()))->toBeFalse();

        Artisan::call(command: 'make:action StoreUserAction');
        Artisan::call(command: 'make:action StoreUserAction --force');

        expect(value: fileExists(relativeFileName: $fileName, path: actionPath()))->toBeTrue()
            ->and(
                value: Str::contains(
                    haystack: fileGet(relativeFileName: $fileName, path: actionPath()),
                    needles: ['{{ class }}', '{{ namespace }}']
                )
            )->toBeFalse();
    })
    ->group(groups: 'commands');

it(description: 'cannot generate the Action, if the Action already exists')
    ->defer(function () {
        $fileName = '/StoreUserAction.php';

        expect(value: fileExists(relativeFileName: $fileName, path: actionPath()))->toBeFalse();

        Artisan::call(command: 'make:action StoreUserAction');

        expect(value: fileExists(relativeFileName: $fileName, path: actionPath()))->toBeTrue();

        Artisan::call(command: 'make:action StoreUserAction');

        expect(value: fileExists(relativeFileName: $fileName, path: actionPath()))->toBeFalse();
    })
    ->group(groups: 'commands')
    ->throws(exception: FileAlreadyExistException::class);
