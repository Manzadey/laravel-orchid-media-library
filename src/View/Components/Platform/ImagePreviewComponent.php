<?php

declare(strict_types=1);

namespace Manzadey\OrchidMediaLibrary\View\Components\Platform;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ImagePreviewComponent extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public $media)
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render() : View
    {
        return view('orchid-laravel-media-library::components.platform.image-preview-component');
    }
}
