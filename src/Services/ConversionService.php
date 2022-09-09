<?php

declare(strict_types=1);

namespace Manzadey\OrchidMediaLibrary\Services;

use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;

class ConversionService
{
    /**
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     */
    public static function platformConversion(HasMedia $media) : void
    {
        $media->addMediaConversion('platform')
            ->keepOriginalImageFormat()
            ->width(100)
            ->crop(Manipulations::CROP_CENTER, 100, 100)
            ->optimize()
            ->quality(70)
            ->nonQueued();
    }

    /**
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     */
    public static function openGraphConversion(HasMedia $media, string $crop = Manipulations::CROP_CENTER) : void
    {
        $media->addMediaConversion('opengraph')
            ->keepOriginalImageFormat()
            ->width(128)
            ->crop($crop, 128, 128)
            ->optimize()
            ->nonQueued();
    }
}
