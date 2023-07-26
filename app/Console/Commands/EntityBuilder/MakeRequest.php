<?php

namespace App\Console\Commands\EntityBuilder;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Pluralizer;

class MakeRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:entity-request {entity}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add request for especific entity';


    protected $entity;

    /**
     * Filesystem instance
     * @var Filesystem
     */
    protected $files;

    /**
     * Create a new command instance.
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }


    /**
    **
    * Map the stub variables present in stub to its value
    *
    * @return array
    *
    */
    public function getStubVariables()
    {
        $name =  preg_replace("/[^a-zA-Z]/", "", $this->entity['name']);
        $name = ucfirst($name);
        return [
            'namespace'       => 'App\\Http\\Requests',
            'class'           => $name,
            'validators'     => $this->getValidators(),
        ];
    }

    /**
     * Get validators
     *
     * @return bool|mixed|string
     *
     */
    public function getValidators()
    {


        $string = '';
        foreach ($this->entity['fields'] as $field) {
            if(isset($field['validations']['back'])) {
                $string .= "'".$field["name"]."' => '".$field['validations']['back']."',\n\t\t";
            }
        }
        return $string;
    }

     /**
     * Get the stub path and the stub variables
     *
     * @return bool|mixed|string
     *
     */
    public function getSourceFile()
    {
        return $this->getStubContents($this->getStubPath(), $this->getStubVariables());
    }



    /**
     * Replace the stub variables(key) with the desire value
     *
     * @param $stub
     * @param array $stubVariables
     * @return bool|mixed|string
     */
    public function getStubContents($stub , $stubVariables = [])
    {
        $contents = file_get_contents($stub);

        foreach ($stubVariables as $search => $replace)
        {
            $contents = str_replace('{{ '.$search.' }}' , $replace, $contents);
        }

        return $contents;

    }

    /**
     * Get the full path of generate class
     *
     * @return string
     */
    public function getSourceFilePath()
    {
        $name =  preg_replace("/[^a-zA-Z]/", "", $this->entity['name']);
        $name = ucfirst($name);
        return base_path('App\\Http\\Requests') .'\\' . $name . 'Request.php';
    }

    /**
     * Return the Singular Capitalize Name
     * @param $name
     * @return string
     */
    public function getSingularClassName($name)
    {
        return ucwords(Pluralizer::singular($name));
    }

    /**
     * Return the Plural Capitalize Name
     * @param $name
     * @return string
     */
    public function getPluralClassName($name)
    {
        return ucwords(Pluralizer::plural($name));
    }

    /**
     * Return the stub file path
     * @return string
     *
     */
    public function getStubPath()
    {
        return __DIR__ . '/../../../../stubs/entity-request.stub';
    }


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->entity = json_decode($this->argument('entity'), true);
        $path = $this->getSourceFilePath();

        $contents = $this->getSourceFile();

        if (!$this->files->exists($path)) {
            $this->files->put($path, $contents);
            $this->info("File : {$path} created");
        } else {
            $this->error('Request already exists');
        }

    }
}
