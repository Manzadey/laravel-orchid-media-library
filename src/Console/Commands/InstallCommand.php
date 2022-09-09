<?php

declare(strict_types=1);

namespace Manzadey\OrchidMediaLibrary\Console\Commands;

use Illuminate\Console\Command;
use Manzadey\OrchidMediaLibrary\Providers\FoundationServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

class InstallCommand extends Command
{
    protected $signature = 'orchid-media-library:install';

    protected $description = 'Install Laravel Orchid Media Library Wrapper';

    public function handle() : int
    {
        $this->callVendorPublish(FoundationServiceProvider::class, [
            'orchid-media-library-stubs',
        ]);

        if($this->confirm('You need to publish the migration to create the media table?', true)) {
            $this->callVendorPublish(MediaLibraryServiceProvider::class, [
                'migrations',
            ]);
        }

        if($this->confirm('Publishing the config file?', true)) {
            $this->callVendorPublish(MediaLibraryServiceProvider::class, [
                'config',
            ]);
        }

        $this->components->info('Package installed!');

        return 1;
    }

    private function callVendorPublish(string $provider, array $tags) : void
    {
        $this->call('vendor:publish', [
            '--provider' => $provider,
            '--tag'      => $tags,
            '--force'    => true,
        ]);
    }
}
