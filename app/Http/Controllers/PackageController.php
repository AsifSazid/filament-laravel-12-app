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
        $package = Str::lower($data['package']);
        $namespace = ucfirst($vendor) . "\\" . ucfirst($package);

        $basePath = base_path("packages/{$vendor}/{$package}");
        $srcPath = $basePath . "/src";

        // 1. Create folder structure
        File::makeDirectory($srcPath, 0755, true, true);

        // 2. composer.json
        $composer = [
            "name" => "{$vendor}/{$package}",
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
                    "{$namespace}\\" => "src/"
                ]
            ],
            "autoload-dev" => (object)[],
            "extra" => [
                "copyright" => $data['author_website'] ?? '',
                "laravel" => [
                    "providers" => [
                        "{$namespace}\\" . ucfirst($package) . "ServiceProvider"
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

namespace {$namespace};

use Illuminate\Support\ServiceProvider;

class {$package}ServiceProvider extends ServiceProvider
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
        \$this->loadViewsFrom(__DIR__ . '/../resources/views', '{$package}');

        \$this->publishes([
            __DIR__.'/../publishable/assets' => public_path('vendor/{$package}'),
        ], 'public');
    }
}
PHP;

        File::put("{$srcPath}/" . ucfirst($package) . "ServiceProvider.php", $serviceProvider);

        // 4. README.md and LICENSE
        File::put("{$basePath}/README.md", "# {$package}\n\n{$data['description']}");
        File::put("{$basePath}/LICENSE", "MIT License");

        // 5. Create ZIP
        $zipFile = storage_path("app/{$vendor}-{$package}.zip");
        $zip = new ZipArchive;

        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($basePath),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($basePath) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }
            $zip->close();
        }

        return response()->download($zipFile)->deleteFileAfterSend(true);
    }
}
