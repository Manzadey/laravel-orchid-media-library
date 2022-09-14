<?php

declare(strict_types=1);

namespace Manzadey\OrchidMediaLibrary\Services;

class MediaService
{
    public const NAME       = 'Медиа';

    public const ICON       = 'film';

    public const PLURAL     = 'media';

    public const ROUTE      = 'platform.' . self::PLURAL . '.';

    public const ROUTE_LIST = self::ROUTE . 'list';

    public const ROUTE_SHOW = self::ROUTE . 'show';

    public const ROUTE_EDIT = self::ROUTE . 'edit';
}
