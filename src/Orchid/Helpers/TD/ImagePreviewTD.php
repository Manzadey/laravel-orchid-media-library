<?php

declare(strict_types=1);

namespace Manzadey\OrchidMediaLibrary\Orchid\Helpers\TD;

use Manzadey\OrchidMediaLibrary\View\Components\Platform\ImagePreviewComponent;
use Orchid\Screen\TD;

class ImagePreviewTD
{
    public static function make() : TD
    {
        return TD::make('media', __('Превью'))
            ->component(ImagePreviewComponent::class)
            ->width('50px')
            ->alignCenter();
    }
}
