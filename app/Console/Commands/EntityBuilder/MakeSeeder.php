<?php

namespace App\Console\Commands\EntityBuilder;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Pluralizer;
use Illuminate\Console\Command;

class MakeSeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:entity-seeder {entity}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add seeder for especific entity';


    /**
     * Filesystem instance
     * @var Filesystem
     */
    protected $files;

    protected $entity;

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
            'namespace' => 'Database\\Seeders' ,
            'class'     => $name,
            'tableName' =>  $this->getTableName(),
            'columns'   =>  $this->getColumnsString(),
        ];
    }


    /**
     * Get the string of the columns to be added to the Seeder
     *
     * @return string
     */
    public function getColumnsString()
    {

        $string = '';
        foreach ($this->entity['fields'] as $field) {
            if($field['editable']) {
                $string .= "'" . $field['name'] . "' => \$faker->";

                // If String
                if($field['input_type']['sql_type']==='string'){
                    $string .= "text(";
                    if(isset($field['length'])){
                        $string .= $field['length'];
                    }
                    $string .= ")";
                }

                // If Integer
                if($field['input_type']['sql_type']==='integer'){
                    $string .= "numberBetween(1,100)";
                }


                $string .= ",\n\t\t\t\t";
            }
        }

        return $string;
    }


    /**
     * Get the table name
     *
     * @return string
     *
     */
    public function getTableName()
    {
        // Keep in mind that if name contains more than one word, it must be separated by underscore and pluralize. Examp: UserGroup => user_groups
        // First we get the name of the entity
        $name = $this->entity['name'];
        // Then separate the words by uppercase letters
        $name = preg_split('/(?=[A-Z])/', $name, -1, PREG_SPLIT_NO_EMPTY);
        // Then we join the words with underscore
        $name = implode('_', $name);
        // Finally we pluralize the name
        $name = Pluralizer::plural($name);
        // And return the name in lowercase
        return strtolower($name);
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
        return base_path('database\\seeders') .'\\' .ucfirst($this->entity['name']) . 'Seeder.php';
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
        $name =  preg_replace("/[^a-zA-Z]/", "", $name);

        return ucwords($name);
    }

    /**
     * Return the stub file path
     * @return string
     *
     */
    public function getStubPath()
    {
        return __DIR__ . '/../../../../stubs/entity-seeder.stub';
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
            $this->error('Seeder already exists');
        }

    }
}
