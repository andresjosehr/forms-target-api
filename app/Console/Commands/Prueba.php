<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Prueba extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:prueba {--file=} {--entity=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // echo $this->option('name');
        echo $this->option('label');
        return Command::SUCCESS;
    }
}
