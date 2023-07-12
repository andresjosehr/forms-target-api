<?php

namespace App\Console\Commands\EntityBuilder;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Pluralizer;
use Illuminate\Console\Command;

class MakeModel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:entity-model {entity}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add model for especific entity';


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
            'namespace'       => 'App\\Models',
            'class'           => $this->getSingularClassName($this->entity['name']),
            'fillAbleColumns' => $this->getFillableColumns(),
            'relationships'   => $this->getRelationships(),
        ];
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

    public function getRelationships($string = '')
    {
        if($this->entity['layout'] == 2){
            $string .= "\n\n\tpublic function client(){ \n\n\t\t".
                "return \$this->belongsTo(Client::class); \n\n\t".
            "}\n\n\t";

            foreach($this->entity['fields'] as $field){
                if($field['input_type']['name']=='related'){
                    $string .= "\n\n\tpublic function ".Pluralizer::plural($field['related_entity']['name'])."(){ \n\n\t\t".
                        "return \$this->belongsTo(".ucfirst($field['related_entity']['name'])."::class, '".$field['name']."'); \n\n\t".
                    "}\n\n\t";
                }
            }
        }

        return $string;
    }




    /**
     * Get string of fillable columns
     *
     * @return string
     *
     */
    public function getFillableColumns()
    {


        $string = '';
        foreach ($this->entity['fields'] as $field) {
            if($field['editable']) {
                $string .= "'" . $field['name'] . "', ";
            }
        }
        return $string;
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
        return base_path('App\\Models') .'\\' . ucfirst($this->entity['name']) . '.php';
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
        return __DIR__ . '/../../../../stubs/entity-model.stub';
    }


    /**
     * Return the stub file path
     * @return string
     *
     */
    public function addRelationshipToClient($entity)
    {
        $modelRoute = file_get_contents(base_path('App\\Models\\Client.php'));

        if (strpos($modelRoute, 'public function '.Pluralizer::plural($entity['name']).'()') !== false) {
            $this->error('Client relationship already exists');
            return ;
        }


        $model = str_replace(
            '/* Add new relationships here */',
            "public function ".Pluralizer::plural($entity['name'])."(){ \n\n\t".
                "return \$this->hasMany(".ucfirst($this->entity['name'])."::class); \n\n\t".
            "}\n\n\t/* Add new relationships here */",
            $modelRoute
        );

        file_put_contents(base_path('App\\Models\\Client.php'), $model);


        $this->info("Relationship in client model created");
    }






    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->entity = json_decode($this->argument('entity'), true);

        if($this->entity['layout'] == 2){
            self::addRelationshipToClient($this->entity);
        }

        $path = $this->getSourceFilePath();

        $contents = $this->getSourceFile();

        if (!$this->files->exists($path)) {
            $this->files->put($path, $contents);
            $this->info("File : {$path} created");
        } else {
            $this->error('Model already exists');
        }

    }
}
