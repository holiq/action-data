<?php

namespace Tests\Feature\Commands;

use Holiq\ActionData\Exceptions\FileAlreadyExistException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

beforeEach(closure: function () {
    (new Filesystem())
        ->deleteDirectory(directory: dataTransferObjectPath());
});

afterEach(closure: function () {
    (new Filesystem())
        ->deleteDirectory(directory: dataTransferObjectPath());
});

it(description: 'can generate new Data Transfer Object class')
    ->defer(function () {
        $fileName = 'StoreUserData.php';

        expect(value: fileExists(relativeFileName: $fileName, path: dataTransferObjectPath()))->toBeFalse();

        Artisan::call(command: 'make:dto StoreUserData');

        expect(value: fileExists(relativeFileName: $fileName, path: dataTransferObjectPath()))->toBeTrue()
            ->and(
                value: Str::contains(
                    haystack: fileGet(relativeFileName: $fileName, path: dataTransferObjectPath()),
                    needles: ['{{ class }}', '{{ namespace }}']
                )
            )->toBeFalse();
    })
    ->group('commands');

it(description: 'can generate new Data Transfer Object class with separator')
    ->defer(function () {
        $fileName = '/Foo/BarData.php';

        expect(value: fileExists(relativeFileName: $fileName, path: dataTransferObjectPath()))->toBeFalse();

        Artisan::call(command: 'make:dto Foo/BarData');

        expect(value: fileExists(relativeFileName: $fileName, path: dataTransferObjectPath()))->toBeTrue()
            ->and(
                value: Str::contains(
                    haystack: fileGet(relativeFileName: $fileName, path: dataTransferObjectPath()),
                    needles: ['{{ class }}', '{{ namespace }}']
                )
            )->toBeFalse();
    })
    ->group('commands');

it(description: 'can force generate exists Data Transfer Object class')
    ->defer(function () {
        $fileName = '/StoreUserData.php';

        expect(value: fileExists(relativeFileName: $fileName, path: dataTransferObjectPath()))->toBeFalse();

        Artisan::call(command: 'make:dto StoreUserData');
        Artisan::call(command: 'make:dto StoreUserData --force');

        expect(value: fileExists(relativeFileName: $fileName, path: dataTransferObjectPath()))->toBeTrue()
            ->and(
                value: Str::contains(
                    haystack: fileGet(relativeFileName: $fileName, path: dataTransferObjectPath()),
                    needles: ['{{ class }}', '{{ namespace }}']
                )
            )->toBeFalse();
    })
    ->group(groups: 'commands');

it(description: 'cannot generate the Action, if the Data Transfer Object already exists')
    ->defer(function () {
        $fileName = '/StoreUserData.php';

        expect(value: fileExists(relativeFileName: $fileName, path: dataTransferObjectPath()))->toBeFalse();

        Artisan::call(command: 'make:dto StoreUserData');

        expect(value: fileExists(relativeFileName: $fileName, path: dataTransferObjectPath()))->toBeTrue();

        Artisan::call(command: 'make:dto StoreUserData');

        expect(value: fileExists(relativeFileName: $fileName, path: dataTransferObjectPath()))->toBeFalse();
    })
    ->group(groups: 'commands')
    ->throws(exception: FileAlreadyExistException::class);
