<?php

namespace Tests\Feature\Commands;

use Holiq\ActionData\Exceptions\FileAlreadyExistException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

beforeEach(closure: function () {
    (new Filesystem())
        ->deleteDirectory(directory: actionPath());

    (new Filesystem())
        ->deleteDirectory(directory: dataTransferObjectPath());
});

afterEach(closure: function () {
    (new Filesystem())
        ->deleteDirectory(directory: actionPath());

    (new Filesystem())
        ->deleteDirectory(directory: dataTransferObjectPath());
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

it(description: 'can generate an Action and its DTO')
    ->defer(function () {
        Artisan::call(command: 'make:action User/StoreUserAction --with-dto=User/StoreUserData');

        expect(fileExists(relativeFileName: 'User/StoreUserAction.php', path: actionPath()))->toBeTrue()
            ->and(fileExists(relativeFileName: 'User/StoreUserData.php', path: dataTransferObjectPath()))->toBeTrue()
            ->and(fileGet(relativeFileName: 'User/StoreUserAction.php', path: actionPath()))
            ->toContain('use App\\DataTransferObjects\\User\\StoreUserData;')
            ->and(fileGet(relativeFileName: 'User/StoreUserAction.php', path: actionPath()))
            ->toContain('function execute(StoreUserData $data)');
    })
    ->group('commands');

it(description: 'does not create an Action when its DTO already exists')
    ->defer(function () {
        Artisan::call(command: 'make:dto User/StoreUserData');

        expect(fn () => Artisan::call(command: 'make:action User/StoreUserAction --with-dto=User/StoreUserData'))
            ->toThrow(FileAlreadyExistException::class);

        expect(fileExists(relativeFileName: 'User/StoreUserAction.php', path: actionPath()))->toBeFalse();
    })
    ->group('commands');

it(description: 'uses configured paths for Action and DTO generation')
    ->defer(function () {
        config([
            'action-data.action_path' => 'app/CustomActions',
            'action-data.data_path' => 'app/CustomData',
        ]);

        Artisan::call(command: 'make:action Admin/CreateUserAction --with-dto=Admin/CreateUserData');

        expect(fileExists(relativeFileName: 'Admin/CreateUserAction.php', path: actionPath()))->toBeTrue()
            ->and(fileExists(relativeFileName: 'Admin/CreateUserData.php', path: dataTransferObjectPath()))->toBeTrue();
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
