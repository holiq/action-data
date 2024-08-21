# Simple way to use Actions and DTOs on your Laravel project

## Installation

```sh
composer require holiq/action-data
```

### Commands

#### Actions

```sh
php artisan make:action StorePostAction
```

#### DataTransferObjects

```sh
php artisan make:dto StorePostAction
```

### Example used

```php
// DTO
namespace App\DataTransferObjects;

use Holiq\ActionData\Foundation\DataTransferObject;

readonly class CategoryData extends DataTransferObject
{
    public function __construct(
        public string $name,
    ) {}
}

// Action
namespace App\Actions\Category;

use App\DataTransferObjects\CategoryData;
use App\Models\Category;
use Holiq\ActionData\Foundation\Action;

readonly class StoreCategoryAction extends Action
{
    public function execute(CategoryData $data): void
    {
        Category::query()->create($data->toArray());
    }
}

// Controller
namespace App\Http\Controllers\Category;

use App\Actions\Category\StoreCategoryAction;
use App\DataTransferObjects\CategoryData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use CuyZ\Valinor\Mapper\MappingError;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class StoreCategoryController extends Controller
{
    /**
     * @throws MappingError
     */
    public function __invoke(StoreCategoryRequest $storeCategoryRequest): RedirectResponse
    {
        StoreCategoryAction::resolve()->execute(
            data: CategoryData::resolve(data: $storeCategoryRequest->validated())
        );

        return Redirect::back();
    }
}

```
