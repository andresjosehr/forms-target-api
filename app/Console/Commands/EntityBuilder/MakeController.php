<?php

namespace App\Console\Commands\EntityBuilder;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Pluralizer;
use Illuminate\Console\Command;

class MakeController extends Command
{
    /**
     * The name and signature of the console command.
     *
     * *
     * @var string
     */
    protected $signature = 'make:entity-controller {entity}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add controller for especific entity';


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
     * Return the stub file path
     * @return string
     *
     */
    public function getStubPath()
    {
        return __DIR__ . '/../../../../stubs/entity-controller.stub';
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
        $entity = json_decode($this->argument('entity'), true);

        $name=$entity['name'];
        $name =  preg_replace("/[^a-zA-Z]/", "", $name);
        $name = ucfirst($name);
        return [
            'namespace'        => 'App\\Http\\Controllers',
            'class'            => $this->getPluralClassName($entity['name']),
            'name'             => $name,
            'label'            => $entity['label'],
            'camelName'        => $this->getCamelCaseName($entity['name']),
            'editableFields'   => $this->getEditableFieldsSetring(),
            'searchableFields' => $this->getSearchableFieldsSetring(),
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
        $entity = json_decode($this->argument('entity'), true);
        return base_path('App\\Http\\Controllers') .'\\' .$this->getPluralClassName($entity['name']) . 'Controller.php';
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
        return ucwords(Pluralizer::plural($name));
    }


    /**
     * Return the camelCase Name
     * @param $name
     * @return string
     */
    public function getCamelCaseName($name)
    {
        $name =  preg_replace("/[^a-zA-Z]/", "", $name);
        return lcfirst($name);
    }


    /**
     * Return the string used to edit or create fields of the entity
     * @param $name
     * @return string
     */
    public function getEditableFieldsSetring()
    {
        $name = strtolower($this->entity['name']);
        $name =  preg_replace("/[^a-zA-Z]/", "", $name);

        $string = '';
        if($this->entity['layout']==2){
            $string .= "$".$name."->client_id = \$request->client_id;\n\t\t";
        }
        foreach ($this->entity['fields'] as $field) {
            if($field['editable']) {
                if($field['input_type']['name'] == 'date'){
                    $string .= "$$name->$field[name] = explode('T', \$request->$field[name])[0];\n\t\t";
                    continue;
                }
                if($field['input_type']['name'] == 'file'){
                    $string .= "$$name->$field[name] = json_encode(\$request->$field[name]);\n\t\t";
                    continue;
                }
                $string .= "$$name->$field[name] = \$request->$field[name];\n\t\t";
            }
        }
        return $string;
    }

    /**
     * Return the string used to search fields of the entity
     * @param $name
     * @return string
     */
    public function getSearchableFieldsSetring()
    {
        $string = '';
            $string = 'when(($request->input("searchString")!=""), function($q) use ($request){'."\n\t\t\t";
            $string .= '$q';
            foreach ($this->entity['fields'] as $field) {
                if(isset($field['searchable'])) {
                    if($field['searchable']) {
                        $string .= "\n\t\t\t".'->orWhere("'.$field['name'].'", "like", "%".$request->searchString."%")';
                    }
                }
            }
            $string .= ';'."\n\t\t".'})->';

            foreach ($this->entity['fields'] as $field) {
                if(isset($field['searchable'])) {
                    if($field['searchable']) {

                        $f = '$request->'.$field['name'];

                        if($field['input_type']['name'] == 'date'){
                            $f = 'Carbon::parse($request->'.$field['name'].')->format("Y-m-d")';
                        }


                        $string .= "\n\t\t".'when(($request->input("'.$field['name'].'")!=""), function($q) use ($request){'."\n\t\t\t";
                        $string .= '$q->where("'.$field['name'].'", "like", "%".'.$f.'."%");';
                        $string .= "\n\t\t".'})->';
                    }
                }
            }

            foreach ($this->entity['fields'] as $field) {
                if($field['input_type']['name']=='related'){
                    $string .= 'with("'.Pluralizer::plural($field['related_entity']['name']).'")->';
                }
            }

        return $string;
    }



    /**
     * Build the directory for the class if necessary.
     *
     * @param  string  $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }

        return $path;
    }

    public function addRelationshipToClient($entity)
    {
        $controllerRoute = file_get_contents(base_path('App\\Http\\Controllers\\ClientsController.php'));




        $str = '';
        foreach($entity['fields'] as $field){
            if($field['input_type']['name']=='related'){
                if(strpos($controllerRoute, "->with('".Pluralizer::plural($entity['name']).".".Pluralizer::plural($field['related_entity']['name'])."')") === false){
                    $str .= "->with('".Pluralizer::plural($entity['name']).".".Pluralizer::plural($field['related_entity']['name'])."') \n\t\t";
                }else {
                    $this->error("Relationship in client controller already exists: ".$field['name']);
                }
            }
        }

        $str .= "/* Add new relationships here */ \n\t\t";
        $controller = str_replace(
            '/* Add new relationships here */',
            $str,
            $controllerRoute
        );

        file_put_contents(base_path('App\\Http\\Controllers\\ClientsController.php'), $controller);


        $this->info("Relationship in client controller created");
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

        $this->makeDirectory(dirname($path));

        $contents = $this->getSourceFile();

        if (!$this->files->exists($path)) {
            $this->files->put($path, $contents);
            $this->info("File : {$path} created");
        } else {
            $this->error('Controller already exists');
        }

    }

}
