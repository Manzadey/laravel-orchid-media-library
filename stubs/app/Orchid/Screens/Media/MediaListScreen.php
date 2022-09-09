<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Media;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use JetBrains\PhpStorm\ArrayShape;

class MediaListScreen extends AbstractMediaListScreen
{
    #[ArrayShape(['models' => LengthAwarePaginator::class])]
    public function query() : iterable
    {
        return [
            'models' => $this
                ->getBuilder()
                ->paginate(),
        ];
    }
}
