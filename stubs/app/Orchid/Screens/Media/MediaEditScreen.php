<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Media;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Manzadey\LaravelOrchidHelpers\Orchid\Helpers\Alerts\SaveAlert;
use Manzadey\LaravelOrchidHelpers\Orchid\Helpers\Screens\EditScreen;
use Manzadey\OrchidMediaLibrary\Models\Media;
use Orchid\Screen\Fields\Input;
use Orchid\Support\Facades\Layout;

/**
 * @property Media $model
 */
class MediaEditScreen extends EditScreen
{
    /**
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function query(Media $media) : iterable
    {
        return $this->model($media);
    }

    public function layout() : iterable
    {
        return [
            Layout::rows([
                Input::make($this->field('name'))->title(attrName('name')),
                Input::make($this->field('name_en'))->title(attrName('name') . ' EN'),
                Input::make($this->field('order_column'))->title(attrName('order_column')),
            ]),
        ];
    }

    public function save(Media $media, Request $request) : RedirectResponse
    {
        $media->fill($request->input('model'))->save();

        SaveAlert::make();

        return to_route('platform.media.edit', $media);
    }
}
