<?php

declare(strict_types=1);

namespace Manzadey\OrchidMediaLibrary\Orchid\Screen\Actions\Link;

use Manzadey\OrchidMediaLibrary\Services\MediaService;
use Orchid\Screen\Actions\Link;

class MediaLink
{
    public static function make() : Link
    {
        return Link::make(MediaService::NAME)
            ->icon(MediaService::ICON)
            ->route(MediaService::ROUTE_LIST);
    }
}
