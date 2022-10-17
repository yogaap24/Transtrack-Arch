<?php

namespace Transtrackid\ArchSupport;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ArchSupportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ttid:arch {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make model with architecture from Transtrack';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $stubs = $this->getStubPaths();
        [$model, $database, $paths] = $this->getSourceFilePaths();


        foreach ($paths as $key => $path) {
            $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
            $this->makeDirectory(dirname($path));
            $contents = $this->getSourceFile($stubs[$key], $model, $database);

            if ($this->isFileNotExist($key, $path, $database)) {
                file_put_contents($path, $contents);
                $this->info("File : {$path} created");
            } else {
                $this->error("File : {$path} already exits");
            }
        }
    }

    public function isFileNotExist($key, $path, $database = null)
    {
        if ($key == 'migration') {
            return count(glob(dirname($path) . "/*_create_{$database}_table.php")) == 0;
        } else {
            return !file_exists($path);
        }
    }

    /**
     * Return the stub file path
     * @return array
     *
     */
    public function getStubPaths()
    {
        $path = [];
        $path['entity'] = __DIR__ . '/stubs/entity.stub';
        $path['table'] = __DIR__ . '/stubs/table.stub';
        $path['policy'] = __DIR__ . '/stubs/policy.stub';
        $path['controller'] = __DIR__ . '/stubs/controller.stub';
        $path['service'] = __DIR__ . '/stubs/service.stub';
        $path['migration'] = __DIR__ . '/stubs/migration.stub';
        $path['seeder'] = __DIR__ . '/stubs/seeder.stub';
        return $path;
    }

    /**
     * Get the stub path and the stub variables
     *
     * @return bool|mixed|string
     *
     */
    public function getSourceFile($stub, $model, $database)
    {
        return $this->getStubContents($stub, [
            'model' => $model,
            'database' => $database,
        ]);
    }


    /**
     * Replace the stub variables(key) with the desire value
     *
     * @param $stub
     * @param array $stubVariables
     * @return array|false|string|string[]
     */
    public function getStubContents($stub, array $stubVariables = [])
    {
        $contents = file_get_contents($stub);

        foreach ($stubVariables as $search => $replace) {
            $contents = str_replace('{' . $search . '}', $replace, $contents);
        }

        return $contents;

    }

    /**
     * Get the full path of generate class
     *
     * @return array
     */
    public function getSourceFilePaths()
    {
        $name = $this->argument('name');
        $singular = Str::singular($name);
        $plural = Str::plural($name);
        $model = Str::studly($singular);
        $database = Str::snake($plural);
        $date = Carbon::now()->format('Y_m_d_His');

        $paths = [];
        $paths['entity'] = base_path("app/Models/Entity/$model.php");
        $paths['table'] = base_path("app/Models/Table/{$model}Table.php");
        $paths['policy'] = base_path("app/Policies/{$model}Policy.php");
        $paths['controller'] = base_path("app/Http/Controllers/$model/{$model}Controller.php");
        $paths['service'] = base_path("app/Services/$model/{$model}Service.php");
        $paths['migration'] = base_path("database/migrations/{$date}_create_{$database}_table.php");
        $paths['seeder'] = base_path("database/seeders/{$model}Seeder.php");

        return [
            $model,
            $database,
            $paths,
        ];
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param string $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        return $path;
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootNamespace()
    {
        return $this->laravel->getNamespace();
    }

}
