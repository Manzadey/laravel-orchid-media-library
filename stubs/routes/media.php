<?php

declare(strict_types=1);

use App\Orchid\Screens\Media\MediaEditScreen;
use App\Orchid\Screens\Media\MediaListScreen;
use App\Orchid\Screens\Media\MediaShowScreen;
use Illuminate\Support\Facades\Route;
use Manzadey\OrchidMediaLibrary\Models\Media;
use Manzadey\OrchidMediaLibrary\Services\MediaService;
use Tabuna\Breadcrumbs\Trail;

Route::name('platform.')
    ->group(static function() {
        Route::screen('', MediaListScreen::class)
            ->name('list')
            ->breadcrumbs(static fn(Trail $trail) : Trail => $trail
                ->parent('platform.index')
                ->push(MediaService::NAME, route(MediaService::ROUTE_LIST))
            );

        Route::prefix('{media}')->group(static function() {
            Route::screen('', MediaShowScreen::class)
                ->name('show')
                ->breadcrumbs(static fn(Trail $trail, Media $media) : Trail => $trail
                    ->parent(MediaService::ROUTE_LIST)
                    ->push($media->getAttribute('name'), route(MediaService::ROUTE_SHOW, $media))
                );

            Route::screen('edit', MediaEditScreen::class)
                ->name('edit')
                ->breadcrumbs(static fn(Trail $trail, Media $media) : Trail => $trail
                    ->parent(MediaService::ROUTE_SHOW, $media)
                    ->push(__('Редактировать'), route(MediaService::ROUTE_EDIT, $media))
                );
        });
    });
