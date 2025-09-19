<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Hug;

class ResetHugs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hugs:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset the hugs counter back to zero';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hug = Hug::first();
        if ($hug) {
            $hug->update(['count' => 0]);
            $this->info('Hugs counter has been reset to zero.');
        } else {
            $this->warn('No Hug record found.');
        }
    }
}
