<?php

//Load configuration
$config = include 'config.php';

//Build commands array
$commands = [
    [
        'description' => 'Clean started...',
    ],
    [
        'description' => 'Cleaning...',
        'callback'    => function () use ($config)
        {
            $removes = [
                'vendor',
                'tests',
                'codecept.phar',
                'phpdox.phar',
                'codeception.yml',
                'composer.lock',
                'apigen.phar',
                'phpDocumentor.phar',
                'docs',
                'c3.php',
            ];
            
            foreach ($removes as $remove)
            {
                PackageTemplate\removePath($remove);
            }
            
            PackageTemplate\forceUnlink('.git/index.lock');
        },
    ],
];

//Executing commands and show output
PackageTemplate\executeCommands($commands);
