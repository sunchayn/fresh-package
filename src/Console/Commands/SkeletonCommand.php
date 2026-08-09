<?php

declare(strict_types=1);

namespace VendorName\Skeleton\Console\Commands;

use Illuminate\Console\Command;

class SkeletonCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature = 'skeleton:placeholder';

    /**
     * The command description.
     */
    protected $description = 'Placeholder Artisan command shipped by the package skeleton.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->line('Skeleton placeholder command executed.');

        return self::SUCCESS;
    }
}
