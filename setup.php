<?php

$baseDir = __DIR__;

$folders = [
    'app',
    'app/Controllers',
    'app/Models',
    'app/Views',
    'app/Core',
    'app/Helpers',
    'config',
    'database',
    'database/migrations',
    'database/seeds',
    'public',
    'public/assets',
    'public/assets/css',
    'public/assets/js',
    'public/assets/images',
    'admin',
    'telegram',
    'storage',
    'storage/logs',
    'storage/uploads',
    'routes',
    'tests',
];

$files = [
    '.gitignore' => <<<TXT
/vendor/
/node_modules/
/storage/logs/*
/storage/uploads/*
.env
.DS_Store
Thumbs.db
TXT,
    'README.md' => "# Arvand Audio Store\n",
    'composer.json' => <<<JSON
{
    "name": "arvand/audio-store",
    "description": "Arvand Audio Store - PHP MVC E-commerce Project",
    "type": "project",
    "require": {},
    "autoload": {
        "psr-4": {
            "App\\\\": "app/"
        }
    }
}
JSON,
    'public/index.php' => <<<PHP
<?php

echo "Arvand Audio Store is ready!";
PHP,
    'config/app.php' => <<<PHP
<?php

return [
    'name' => 'Arvand Audio Store',
    'env' => 'local',
];
PHP,
    'routes/web.php' => <<<PHP
<?php

// Web routes will be defined here.
PHP,
];

foreach ($folders as $folder) {
    $path = $baseDir . DIRECTORY_SEPARATOR . $folder;
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
        echo "Created folder: {$folder}\n";
    } else {
        echo "Folder exists: {$folder}\n";
    }
}

foreach ($files as $file => $content) {
    $path = $baseDir . DIRECTORY_SEPARATOR . $file;
    if (!file_exists($path)) {
        file_put_contents($path, $content);
        echo "Created file: {$file}\n";
    } else {
        echo "File exists: {$file}\n";
    }
}

echo "\nDone.\n";
