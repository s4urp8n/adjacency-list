<?php

//Composer classes

include 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

//Load configuration
$config = include 'config.php';

if (!file_exists('tests/_output/coverage.txt'))
{
    die('You must run tests before documentation generation');
}

$commands = [
    [
        'description' => 'Package documenting started...',
    ],
    [
        'callback' => function () use ($config)
        {
            chdir(__DIR__);
        },
    ],
    [
        'callback' => function () use ($config)
        {
            
            $removes = [
                'docs',
            ];
            
            foreach ($removes as $remove)
            {
                PackageTemplate\forceRmdir($remove);
            }
        },
    ],
    [
        'command' => 'php apigen.phar generate --source package/src --destination docs/apigen',
    ],
    [
        'description' => 'Updating gh-pages...',
        'callback'    => function ()
        {
            
            $origin = PackageTemplate\getGitOrigin();
            
            $currentDir = getcwd();
            
            @mkdir('docs/gh-pages', 0777, true);
            chdir('docs/gh-pages');
            
            shell_exec('git init');
            shell_exec('git checkout -b gh-pages');
            
            chdir($currentDir);
            
            PackageTemplate\copyDirectory('docs/apigen', 'docs/gh-pages');
            
            chdir('docs/gh-pages');
            
            shell_exec('git add *');
            shell_exec('git commit -a -m "Autoupdate documentation using Apigen"');
            shell_exec('git remote add origin "' . $origin . '"');
            shell_exec('git push -f origin gh-pages');
            
            chdir($currentDir);
            PackageTemplate\forceRmdir('docs');
            PackageTemplate\forceUnlink('.git/index.lock');
            
        },
    ],
    [
        'callback' => function () use ($config)
        {
            PackageTemplate\updateReadme($config);
        },
    ],
    [
        'callback' => function () use ($config)
        {
            passthru('git add README.md');
        },
    ],
];

//Executing commands and show output
PackageTemplate\executeCommands($commands);