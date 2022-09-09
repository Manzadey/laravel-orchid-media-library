<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Media;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Manzadey\LaravelOrchidHelpers\Orchid\Helpers\Alerts\SaveAlert;
use Manzadey\LaravelOrchidHelpers\Orchid\Helpers\Buttons\SaveButton;
use Manzadey\LaravelOrchidHelpers\Orchid\Helpers\Layouts\ModelsTableLayout;
use Manzadey\LaravelOrchidHelpers\Orchid\Helpers\Links\DeleteLink;
use Manzadey\LaravelOrchidHelpers\Orchid\Helpers\Links\DropdownOptions;
use Manzadey\LaravelOrchidHelpers\Orchid\Helpers\Links\ShowLink;
use Manzadey\LaravelOrchidHelpers\Orchid\Helpers\Screens\AbstractScreen;
use Manzadey\LaravelOrchidHelpers\Orchid\Helpers\TD\ActionsTD;
use Manzadey\LaravelOrchidHelpers\Orchid\Helpers\TD\CreatedAtTD;
use Manzadey\LaravelOrchidHelpers\Orchid\Helpers\TD\EntityRelationTD;
use Manzadey\LaravelOrchidHelpers\Orchid\Helpers\TD\IdTD;
use Manzadey\LaravelOrchidHelpers\Orchid\Helpers\TD\UpdateAtTD;
use Manzadey\LaravelOrchidHelpers\Orchid\Traits\DeleteActionTrait;
use Manzadey\OrchidMediaLibrary\Models\Media;
use Manzadey\OrchidMediaLibrary\Orchid\Helpers\TD\ImagePreviewTD;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Spatie\MediaLibrary\HasMedia;

abstract class AbstractMediaListScreen extends AbstractScreen
{
    use DeleteActionTrait;

    protected bool $multiple = false;

    protected array $collections = [];

    protected array $hiddenColumns = [];

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function layout() : iterable
    {
        $array = array_keys($this->collections);

        if(count($this->collections) === 0) {
            $this->collections = Media::groupBy('collection_name')
                ->withoutGlobalScopes()
                ->get('collection_name')
                ->pluck('collection_name')
                ->mapWithKeys(static fn(string $value) : array => [$value => $value])
                ->toArray();
        }

        return [
            Layout::rows([
                Group::make([
                    Input::make('media[]')
                        ->type('file')
                        ->accept('image/jpeg,image/png,image/gif')
                        ->when($this->multiple, static fn(Input $field) : Input => $field->multiple())
                        ->title('Загрузить файлы'),
                    Select::make('collection')
                        ->title(__('Коллекция'))
                        ->options($this->collections ?? [])
                        ->canSee(count($this->collections) > 1),
                    Input::make('collection')
                        ->type('hidden')
                        ->canSee(count($this->collections) === 1)
                        ->value(array_shift($array)),
                ]),
                Button::make(__('Отправить'))
                    ->icon('save')
                    ->type(Color::DEFAULT())
                    ->method('saveMedia'),
            ])->canSee(method_exists($this, 'saveMedia')),

            ModelsTableLayout::make([
                IdTD::make()->defaultHidden(),
                ImagePreviewTD::make(),
                TD::make('name', attrName('name'))
                    ->render(static fn(Media $media) : Input => Input::make("media[{$media->getAttribute('id')}][name]")
                        ->value($media->getAttribute('name'))
                    ),
                EntityRelationTD::make('model', __('Объект'))
                    ->canSee($this->isHidden('model')),
                TD::make('collection_name', __('Коллекция'))
                    ->defaultHidden()
                    ->sort()
                    ->filter(TD::FILTER_SELECT)
                    ->filterOptions($this->collections),
                TD::make('mime_type', 'MIME')
                    ->defaultHidden(),
                TD::make('human_readable_size', __('Размер')),
                TD::make('order_column', attrName('rating'))
                    ->render(static fn(Media $media) : Input => Input::make("media[$media->id][order_column]")
                        ->type('number')
                        ->min(0)
                        ->value($media->getAttribute('order_column') ?? 0)
                    )
                    ->sort(),
                UpdateAtTD::make(),
                CreatedAtTD::make(),
                ActionsTD::make(static fn(Media $media) : DropDown => DropdownOptions::make()
                    ->list([
                        ShowLink::route('platform.media.show', $media),
                        DeleteLink::makeFromModel($media),
                    ])),
            ]),

            Layout::rows([
                Group::make([
                    SaveButton::make()
                        ->type(Color::DEFAULT())
                        ->method('updateMedia'),

                    Button::make(__('Удалить все фотки'))
                        ->confirm(__('Вы действительно хотите удалить все фотки?'))
                        ->icon('trash')
                        ->method('destroyAllMedia')
                        ->canSee(method_exists($this, 'destroyAllMedia') && $this->authorize('delete', Media::class))
                        ->class('btn btn-danger float-end'),
                ])->alignEnd(),
            ]),
        ];
    }

    /**
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    protected function save(HasMedia $model, Request $request) : void
    {
        $media = $request->file(str_replace('[]', '', 'media'));

        foreach (Arr::wrap($media) as $file) {
            $this->addMedia($model, $file, $request->input('collection', 'default'));
        }

        Alert::success(__('Файлы успешно добавлены!'));
    }

    /**
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    protected function addMedia(HasMedia $media, UploadedFile $file, string $mediaCollection = 'default') : void
    {
        $createMedia = $media
            ->addMedia($file);

        if(method_exists($media, 'generateUsingNameForMedia')) {
            $createMedia->setName($media->generateUsingNameForMedia());
        }

        $createMedia->toMediaCollection($mediaCollection);
    }

    protected function registerMediaCollections($model) : void
    {
        $this->collections = $model
            ->getRegisteredMediaCollections()
            ->pluck('name')
            ->mapWithKeys(
                static fn(string $value) => [$value => __("media-collections.$value")]
            )
            ->toArray();
    }

    public function getBuilder(Media|Builder $builder = null) : Builder
    {
        $models = collect(glob(app_path('Models/*.php')))
            ->map(static fn(string $model) => str($model)
                ->replace([app_path(), '.php', '\\'], '')
                ->prepend('App\\')
                ->replace('/', '\\')
                ->toString()
            );

        return ($builder ?? Media::query())
            ->filters()
            ->when($builder === null, static fn(Builder $builder) : Builder => $builder->whereIn('model_type', $models))
            ->with('model')
            ->oldest('media.order_column');
    }

    private function isHidden(string $key) : bool
    {
        return isset($this->hiddenColumns[$key]);
    }

    public function updateMedia(Request $request) : RedirectResponse
    {
        foreach ($request->input('media', []) as $id => $media) {
            Media::find($id)?->update($media);
        }

        SaveAlert::make();

        return back();
    }
}
