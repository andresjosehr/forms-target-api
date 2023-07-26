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
        // Remove all non-word characters (everything except letters)
        $name =  preg_replace("/[^a-zA-Z]/", "", $name);
        return ucwords(Pluralizer::plural($name));
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


        // $this->call('migrate');

        // Edit the routes file

        // Get the routes/api.php file
        $routes = file_get_contents(base_path('routes/api.php'));
        $routeName = strtolower(str_replace('_', '-', $this->entity['name']));
        // Check if the route already exists
        if (strpos($routes, $routeName) !== false) {
            $this->error('Route already exists');
            return Command::SUCCESS;
        }


        // Replace the /* Add new routes here */ with the new route
        $routes = str_replace(
            '/* Add new routes here */',
            'Route::resource(\'' . $routeName . "', 'App\\Http\\Controllers\\" . $this->getPluralClassName($this->entity['name']) . "Controller');\n\t".
            'Route::get(\'get-all-' . $routeName . "', 'App\\Http\\Controllers\\" . $this->getPluralClassName($this->entity['name']) . "Controller@getAll');\n\n\t/* Add new routes here */",
            $routes
        );

        // Save the routes/api.php file
        file_put_contents(base_path('routes/api.php'), $routes);

        // $this->call('migrate');
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



        return Command::SUCCESS;
    }
}
