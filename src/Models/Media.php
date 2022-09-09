<?php

declare(strict_types=1);

namespace Manzadey\OrchidMediaLibrary\Models;

use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Media extends BaseMedia
{
    use AsSource;
    use Filterable;

    protected array $allowedFilters = [
        'id',
        'name',
        'collection_name',
    ];

    protected array $allowedSorts = [
        'id',
        'name',
        'collection_name',
        'size',
        'order_column',
        'created_at',
        'updated_at',
    ];
}
