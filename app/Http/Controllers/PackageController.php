<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use ZipArchive;
use Illuminate\Support\Str;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class PackageController extends Controller
{
    public $namespace, $package;

    public function generate(Request $request)
    {
        $data = $request->validate([
            'vendor' => 'required|string',
            'package' => 'required|string',
            'description' => 'required|string',
            'author_name' => 'required|string',
            'author_email' => 'required|email',
            'author_website' => 'nullable|string',
        ]);

        $vendor = Str::lower($data['vendor']);
        $this->package = Str::lower($data['package']);
        $this->namespace = ucfirst($vendor) . "\\" . ucfirst($this->package);

        $basePath = base_path("packages/{$vendor}/{$this->package}");
        $srcPath = $basePath . "/src";
        $appPath = $srcPath . "/App";
        $dbPath = $srcPath . "/database";
        $langPath = $srcPath . "/lang";
        $resourcePath = $srcPath . "/resources";

        $this->generateHttp($srcPath);
        $this->generateModel($srcPath);
        $this->generateRoute($srcPath);

        // 1. Create folder structure
        File::makeDirectory($srcPath, 0755, true, true);
        File::makeDirectory($appPath, 0755, true, true);
        File::makeDirectory($dbPath, 0755, true, true);
        File::makeDirectory($langPath, 0755, true, true);
        File::makeDirectory($resourcePath, 0755, true, true);

        // 2. composer.json
        $composer = [
            "name" => "{$vendor}/{$this->package}",
            "type" => "library",
            "description" => $data['description'],
            "keywords" => ["Laravel"],
            "authors" => [[
                "name" => $data['author_name'],
                "email" => $data['author_email'],
                "homepage" => $data['author_website'] ?? '',
            ]],
            "autoload" => [
                "psr-4" => [
                    "{$this->namespace}\\" => "src/"
                ]
            ],
            "autoload-dev" => (object)[],
            "extra" => [
                "copyright" => $data['author_website'] ?? '',
                "laravel" => [
                    "providers" => [
                        "{$this->namespace}\\" . ucfirst($this->package) . "ServiceProvider"
                    ],
                    "aliases" => (object)[]
                ]
            ],
            "version" => "1.0.0",
            "license" => "MIT"
        ];
        File::put("{$basePath}/composer.json", json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // 3. ServiceProvider
        $serviceProvider = <<<PHP
        <?php

        namespace {$this->namespace};

        use Illuminate\Support\ServiceProvider;

        class {$this->package}ServiceProvider extends ServiceProvider
        {
            public function register()
            {
                //
            }

            public function boot()
            {
                \$this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
                \$this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
                \$this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
                \$this->loadViewsFrom(__DIR__ . '/../resources/views', '{$this->package}');

                \$this->publishes([
                    __DIR__.'/../publishable/assets' => public_path('vendor/{$this->package}'),
                ], 'public');
            }
        }
        PHP;

        File::put("{$srcPath}/" . ucfirst($this->package) . "ServiceProvider.php", $serviceProvider);

        // 4. README.md and LICENSE
        File::put("{$basePath}/README.md", "# {$this->package}\n\n{$data['description']}");
        File::put("{$basePath}/LICENSE", "MIT License");

        // 5. Create ZIP
        $zipFile = storage_path("app/{$vendor}-{$this->package}.zip");
        $zip = new ZipArchive;

        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($basePath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
        
            foreach ($files as $file) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($basePath) + 1);
        
                if ($file->isDir()) {
                    $zip->addEmptyDir($relativePath); // 🟢 Add empty directories
                } else {
                    $zip->addFile($filePath, $relativePath); // 🟢 Add files
                }
            }
        
            $zip->close();
        }
        

        return response()->download($zipFile)->deleteFileAfterSend(true);
    }

    private function generateHttp($srcPath)
    {
        $httpPath = $srcPath . "/Http";
        $controllerPath = $httpPath . "/Controllers";
        $apiControllerPath = $controllerPath . "/Api";
        $requestPath = $httpPath . "/Requests";
        $traitPath = $httpPath . "/Traits";
        
        File::makeDirectory($httpPath, 0755, true, true);
        File::makeDirectory($controllerPath, 0755, true, true);
        File::makeDirectory($apiControllerPath, 0755, true, true);
        File::makeDirectory($requestPath, 0755, true, true);
        File::makeDirectory($traitPath, 0755, true, true);

        $sampleController = <<<PHP
        <?php

        namespace $this->namespace\Http\Controllers;

        use App\Http\Controllers\Controller;
        use $this->namespace\Http\Requests\SampleRequest; // If need
        use $this->namespace\Models\SampleModel;
        use Illuminate\Database\QueryException; // If need
        use Illuminate\Support\Str; // If need
        //use another classes

        class SampleController extends Controller
        {
            public function index()
            {}
            public function create()
            {}
            public function store()
            {}
            public function show()
            {}
            public function edit()
            {}
            public function update()
            {}
            public function destroy()
            {}
            public function trash()
            {}
            public function restore()
            {}
            public function forceDelete()
            {}
            public function getData()
            {}
            public function downloadPdf()
            {}
        }

        PHP;
        
        
        $apiSampleController = <<<PHP
        <?php

        namespace $this->namespace\Http\Controllers\Api;

        use App\Http\Controllers\Controller;
        use $this->namespace\Http\Requests\SampleRequest; // If need
        use $this->namespace\Models\SampleModel;
        use Illuminate\Database\QueryException; // If need
        use Illuminate\Support\Str; // If need
        //use another classes

        class SampleController extends Controller
        {
            public function index()
            {}
            public function create()
            {}
            public function store()
            {}
            public function show()
            {}
            public function edit()
            {}
            public function update()
            {}
            public function destroy()
            {}
            public function trash()
            {}
            public function restore()
            {}
            public function forceDelete()
            {}
            public function getData()
            {}
            public function downloadPdf()
            {}
        }

        PHP;

        $sampleRequest = <<<PHP
        <?php

        namespace $this->namespace\Http\Requests;

        use Illuminate\Foundation\Http\FormRequest;
        //use another classes

        class SampleRequest extends FormRequest
        {
            public function authorize()
            {
                return true;
            }
        
            public function rules()
            {
                return [];
            }
        }

        PHP;

        File::put("{$controllerPath}/" . "SampleController.php", $sampleController);
        File::put("{$apiControllerPath}/" . "apiSampleController.php", $apiSampleController);
        File::put("{$requestPath}/" . "sampleRequest.php", $sampleRequest);
    }

    private function generateModel($srcPath)
    {
        $modelPath = $srcPath . "/Models";
        
        File::makeDirectory($modelPath, 0755, true, true);

        $sampleModel = <<<PHP
        <?php

        namespace $this->namespace\Models;

        use Illuminate\Database\Eloquent\Model;
        use Illuminate\Database\Eloquent\SoftDeletes;

        use App\Traits\Historiable; // Not Neccessary

        class SampleModel extends Model
        {
            use SoftDeletes;
    
            // use Historiable; // Not Neccessary
            
            protected \$connection = 'conntection_name';
            protected \$table = 'table_name';
            protected \$guarded = [];
        }

        PHP;

        File::put("{$modelPath}/" . "sampleModel.php", $sampleModel);
    }

    private function generateRoute($srcPath)
    {
        $routePath = $srcPath . "/routes";
        
        File::makeDirectory($routePath, 0755, true, true);

        $webRoute = <<<PHP
        <?php
        use Illuminate\Support\Facades\Route;

        PHP;

        $apiRoute = <<<PHP
        <?php
        use Illuminate\Support\Facades\Route;

        Route::group(['middleware' => 'api', 'prefix' => 'api', 'as' => 'api.'], function () {

        });
        PHP;

        File::put("{$routePath}/" . "api.php", $apiRoute);
        File::put("{$routePath}/" . "route.php", $webRoute);
    }
}
