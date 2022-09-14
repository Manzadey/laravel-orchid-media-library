<?php

namespace App\Orchid\Screens\Media;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\ArrayShape;
use Manzadey\LaravelOrchidHelpers\Orchid\Helpers\Layouts\ModelLegendLayout;
use Manzadey\LaravelOrchidHelpers\Orchid\Helpers\Layouts\ModelTimestampsLayout;
use Manzadey\LaravelOrchidHelpers\Orchid\Helpers\Links\DeleteLink;
use Manzadey\LaravelOrchidHelpers\Orchid\Helpers\Links\DropdownOptions;
use Manzadey\LaravelOrchidHelpers\Orchid\Helpers\Links\EditLink;
use Manzadey\LaravelOrchidHelpers\Orchid\Helpers\Screens\ModelScreen;
use Manzadey\LaravelOrchidHelpers\Orchid\Helpers\Sights\EntitySight;
use Manzadey\LaravelOrchidHelpers\Orchid\Helpers\Sights\IdSight;
use Manzadey\LaravelOrchidHelpers\Orchid\Helpers\Sights\Sight;
use Manzadey\LaravelOrchidHelpers\Orchid\Helpers\TD\BoolTD;
use Manzadey\LaravelOrchidHelpers\Orchid\Helpers\TD\LinkTD;
use Manzadey\LaravelOrchidHelpers\Orchid\Traits\DeleteActionTrait;
use Manzadey\OrchidMediaLibrary\Models\Media;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Repository;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Spatie\MediaLibrary\Conversions\FileManipulator;
use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidConversion;
use Spatie\MediaLibrary\Support\File;

/**
 * @property Media $model
 */
class MediaShowScreen extends ModelScreen
{
    use DeleteActionTrait;

    /**
     * Query data.
     *
     * @return array
     */
    #[ArrayShape([0 => 'iterable', 'generated_conversions' => 'iterable'])]
    public function query(Media $media) : iterable
    {
        return [
            ...$this->model($media->loadCount('activities')),
            'generated_conversions' => collect($media->getAttribute('generated_conversions'))
                ->keys()
                ->map(static function(string $conversion) use ($media) : Repository {
                    $data = [
                        'conversion' => $conversion,
                        'url'        => null,
                        'size'       => null,
                        'generated'  => false,
                    ];

                    try {
                        $data['url']       = $media->getUrl($conversion);
                        $data['size']      = file_exists($media->getPath($conversion)) ? File::getHumanReadableSize(filesize($media->getPath($conversion))) : null;
                        $data['generated'] = $media->hasGeneratedConversion($conversion);
                    } catch (InvalidConversion) {
                    }

                    return new Repository($data);
                }),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name() : ?string
    {
        return $this->model->getAttribute('name');
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar() : iterable
    {
        return [
            DropdownOptions::make()->list([
                Button::make(__('Регенерация'))
                    ->icon('reload')
                    ->method('regenerate', [
                        'media' => $this->model->getAttribute('id'),
                    ]),
                EditLink::route('platform.media.edit', $this->model),
                DeleteLink::makeFromModel($this->model),
            ]),
        ];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout() : iterable
    {
        return [
            Layout::view('platform.media.show', [
                'src' => $this->model->getFullUrl(),
            ]),

            ModelLegendLayout::make([
                IdSight::make(),
                Sight::make('uuid'),
                EntitySight::make('model', __('Объект')),
                Sight::make('name'),
                Sight::make('originalUrl', __('Ссылка'))
                    ->render(static fn(Media $media) : Link => Link::make($media->originalUrl)
                        ->icon('link')
                        ->target('_blank')
                        ->href($media->originalUrl)
                    ),
                Sight::make('file_name', __('Название файла')),
                Sight::make('mime_type', 'MIME'),
                Sight::make('human_readable_size', __('Размер')),
                Sight::make('disk', 'Файловая система'),
                Sight::make('conversions_disk', __('Conversion disk')),
                Sight::make('order_column', __('Порядок')),
                Sight::make('collection_name', __('Коллекция')),
            ]),

            ModelTimestampsLayout::make(),

            Layout::table('generated_conversions', [
                BoolTD::make('generated', __('Сгенерирован')),
                TD::make('conversion')->alignLeft(),
                LinkTD::make('url'),
                TD::make('size')->alignRight(),
            ])
                ->title(__('Преобразования')),
        ];
    }

    public function regenerate(Request $request, FileManipulator $fileManipulator) : RedirectResponse
    {
        $media = Media::query()->find($request->input('media'));

        if($media instanceof Media) {
            $fileManipulator->createDerivedFiles($media);

            Alert::success(__('Изображение обновлено!'));
        }

        return back();
    }
}
