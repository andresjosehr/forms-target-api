<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Pluralizer;



class BuildEntity extends Command
{
    protected $entity;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:entity {--file=} {--entity=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add controller, model, route and migration for an entity';


    /**
     * Return the Plural Capitalize Name
     * @param $name
     * @return string
     */
    public function getPluralClassName($name)
    {
        return ucwords(Pluralizer::plural($name));
    }

    public function builfFromFile()
    {
        $name = strtolower($this->option('file'));
        $file = storage_path('app\\public\\entities-schemas\\' . $name . '.json');
        $content = file_get_contents($file);
        $entity = json_decode($content, true);

        $this->call('make:entity-controller', [
            'entity' => $entity
        ]);

        $this->call('make:entity-request', [
            'entity' => $entity
        ]);

        $this->call('make:entity-model', [
            'entity' => $entity
        ]);

        $this->call('make:entity-migration', [
            'entity' => $entity
        ]);

         $this->call('make:entity-seeder', [
            'entity' => $entity
        ]);

        // Edit the routes file

        // Get the routes/api.php file
        $routes = file_get_contents(base_path('routes/api.php'));

        // Check if the route already exists
        if (strpos($routes, ($this->entity['name'])) !== false) {
            $this->error('Route already exists');
            return Command::SUCCESS;
        }

        // Replace the /* Add new routes here */ with the new route
        // Change _ to -
        $routeName = strtolower(str_replace('_', '-', $routes));


        $routes = str_replace(
            '/* Add new routes here */',
            'Route::resource(\'' . $routeName . "', 'App\\Http\\Controllers\\" . $this->getPluralClassName($this->entity['name']) . "Controller');\n\t".
            'Route::get(\'get-all-' . $routeName . "', 'App\\Http\\Controllers\\" . $this->getPluralClassName($this->entity['name']) . "Controller@getAll');\n\n\t/* Add new routes here */",
            $routes
        );

        // Save the routes/api.php file
        file_put_contents(base_path('routes/api.php'), $routes);
    }

    public function builfFromObject(){

        $this->call('make:entity-controller', [
            'entity' => $this->option('entity')
        ]);

        $this->call('make:entity-request', [
            'entity' => $this->option('entity')
        ]);

        $this->call('make:entity-model', [
            'entity' => $this->option('entity'),
        ]);

        $this->call('make:entity-migration', [
            'entity' => $this->option('entity'),
        ]);

         $this->call('make:entity-seeder', [
            'entity' => $this->option('entity'),
        ]);


        $this->call('migrate');

        // Edit the routes file

        // Get the routes/api.php file
        $routes = file_get_contents(base_path('routes/api.php'));

        // Check if the route already exists
        if (strpos($routes, $this->getPluralClassName($this->entity['name'])) !== false) {
            $this->error('Route already exists');
            return Command::SUCCESS;
        }

        // Replace the /* Add new routes here */ with the new route
        $routes = str_replace(
            '/* Add new routes here */',
            'Route::resource(\'' . strtolower($this->entity['name'])) . "', 'App\\Http\\Controllers\\" . $this->getPluralClassName($this->entity['name']) . "Controller');\n\t".
            'Route::get(\'get-all-' . strtolower($this->entity['name']) . "', 'App\\Http\\Controllers\\" . $this->getPluralClassName($this->entity['name']) . "Controller@getAll');\n\n\t/* Add new routes here */",
            $routes
        );

        // Save the routes/api.php file
        file_put_contents(base_path('routes/api.php'), $routes);
    }


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        // if($this->option('file')) {
        //     self::builfFromFile();
        // }

        if($this->option('entity')) {
            $this->entity = json_decode($this->option('entity'), true);
            self::builfFromObject();
        }

        // Make php artisan migrate
        // $this->call('migrate');


        return Command::SUCCESS;
    }
}
