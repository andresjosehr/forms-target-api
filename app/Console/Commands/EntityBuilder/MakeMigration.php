<?php

namespace App\Console\Commands\EntityBuilder;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Console\Command;
use Illuminate\Support\Pluralizer;
use Nette\Utils\FileSystem as UtilsFileSystem;

class MakeMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:entity-migration {entity}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add migration for especific entity';

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
        return [
            'entityName' => $this->entity['name'],
            'table'   => $this->getTableName(),
            'columns' => $this->getColumnsString(),
        ];
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
        // // Then separate the words by uppercase letters
        // $name = preg_split('/(?=[A-Z])/', $name, -1, PREG_SPLIT_NO_EMPTY);
        // // Then we join the words with underscore
        // $name = implode('_', $name);
        // // Finally we pluralize the name
        // $name = $name;
        // // And return the name in lowercase
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
        // Make migration file name as create_{table_name}_table including timestamp as laravel make:migration command does
        $fileName = 'create_' . $this->getTableName() . '_table.php';
        $fileName = date('Y_m_d_His') . '_' . $fileName;
        return base_path('database\\migrations') .'\\' .$fileName . '.php';
    }


    /**
     * Get the string of the columns to be added to the migration
     *
     * @return string
     */
    public function getColumnsString()
    {

        $string = '';

        if($this->entity['layout']==2){
            $string .= '$table->bigInteger("client_id");';
            $string .= "\n\t\t\t";
        }

        foreach ($this->entity['fields'] as $field) {



            if($field['name']=='id' || $field['name']=='ID' || $field['name']=='Id') {
                continue;
            }

            if($field['input_type']['name']=='related') {
                $string .= '$table->bigInteger("'.$field['name'].'")->unsigned()->nullable();';
                continue;
            }

            // if($field['sql_properties']['sql_type']['name']==='relatedEntity') {
            //     $string .= self::addRelatedColumns($field);
            //     continue;
            // }

            $string .= '$table->';
            $string .= $field['input_type']['sql_type'];
            $string .= '("'. $field['name'].'")';

            if($field['input_type']['name']=='checkbox') {
                $string .= '->default(0)';
            }


            $required = false;
            foreach($field['validations'] as $validation) {
                if($validation['name']==='required') {
                    $required = true;
                }
            }

            if(!$required) {
                $string .= '->nullable()';
            }

            $string .= ";\n\t\t\t";

        }

        return $string;
    }

    static public function addRelatedColumns($field)
    {
            $column = strtolower($field['sql_properties']['related_entity']['name']). '_id';
            $tableName = Pluralizer::plural(strtolower($field['sql_properties']['related_entity']['name']). 's');

            $string = '';
            $string .= '$table->bigInteger("'.$column.'")->unsigned()->nullable();';
            $string .= "\n\t\t\t";
            $string .= '$table->foreign("'.$column.'")->references("id")->on("'.$tableName.'");';

            return $string;
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
        return __DIR__ . '/../../../../stubs/entity-migration.stub';
    }


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->entity = json_decode($this->argument('entity'), true);
        // Get file list from the migrations folder
        $fileList = $this->files->files(base_path('database\\migrations'));

        foreach ($fileList as $file) {
            // Get the file name
            $fileName = $file->getFilename();
            // Get the file path
            $filePath = $file->getPathname();
            // Get the file content
            $fileContent = $this->files->get($filePath);

            // If the file name contains the table name
            if (strpos($fileName, $this->getTableName()) !== false) {
                // If the file content contains the table name
                if (strpos($fileContent, $this->getTableName()) !== false) {
                    // Then the migration already exists
                    $this->error('Migration already exists');
                    return;
                }
            }
        }

        $path = $this->getSourceFilePath();

        $contents = $this->getSourceFile();
        $this->files->put($path, $contents);
        $this->info("File : {$path} created");


    }
}
