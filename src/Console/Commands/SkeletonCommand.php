<?php

declare(strict_types=1);

namespace VendorName\Skeleton\Console\Commands;

use Illuminate\Console\Command;
use VendorName\Skeleton\Modules\Greeting\Actions\BuildGreetingAction;
use VendorName\Skeleton\Modules\Greeting\DataTransferObjects\GreetingData;
use VendorName\Skeleton\Modules\Greeting\Enums\GreetingTone;

class SkeletonCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature = 'skeleton:placeholder {name=World} {--tone=casual : formal or casual}';

    /**
     * The command description.
     */
    protected $description = 'Placeholder Artisan command shipped by the package skeleton.';

    /**
     * Execute the console command.
     */
    public function handle(BuildGreetingAction $buildGreetingAction): int
    {
        $data = new GreetingData(
            name: $this->argument('name'),
            tone: GreetingTone::from((string) $this->option('tone')),
        );

        $this->line($buildGreetingAction->execute($data));

        return self::SUCCESS;
    }
}
