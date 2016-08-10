<?php

//Composer classes
include 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

//Load configuration
$config = include 'config.php';

$testResult = null;

if (PackageTemplate\isPagesExists())
{
    
    $webServerRoot = __DIR__ . DIRECTORY_SEPARATOR . 'package' . DIRECTORY_SEPARATOR . 'pages';
    $webServerRouter = __DIR__ . DIRECTORY_SEPARATOR . 'router.php';
    $webServerCommand = 'php -S ' . $config['server'] . ' -t "' . $webServerRoot . '" "' . $webServerRouter . '"';
    
    echo "\nWebserver command is :" . $webServerCommand . "\n";
    
    $webServerProcess = proc_open(
        $webServerCommand, [
        ["pipe", "r"],
        ["pipe", "w"],
        ["pipe", "w"],
    ], $pipesWebServer
    );
    
    echo "Webserver loading...";
    while (!is_resource($webServerProcess))
    {
        echo ".";
    }
    echo "\n";
}

$commands = [
    [
        'description' => 'Package testing started...',
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
                'tests',
                'docs',
                'codeception.yml',
            ];
            
            foreach ($removes as $remove)
            {
                PackageTemplate\removePath($remove);
            }
        },
    ],
    [
        'command' => 'php codecept.phar bootstrap',
    ],
    [
        'description' => 'Replace testing files...',
        'callback'    => function () use ($config)
        {
            PackageTemplate\copyDirectory('package/tests', 'tests');
            unlink('codeception.yml');
            copy('package/codeception.yml', 'codeception.yml');
        },
    ],
    [
        'command' => 'php codecept.phar build',
    ],
    [
        'description' => 'Testing...',
        'callback'    => function () use ($config, &$testResult)
        {
            if (PackageTemplate\isPagesExists())
            {
                $testCommand = 'php codecept.phar run acceptance';
                passthru($testCommand, $acceptanceResult);
            }
            
            $testCommand = 'php codecept.phar run unit --coverage-xml --coverage-html --coverage-text --coverage';
            passthru($testCommand, $testResult);
            
            if (PackageTemplate\isPagesExists())
            {
                $testResult = intval($acceptanceResult) + intval($testResult);
            }
            
        },
    ],
];

//Executing commands and show output
PackageTemplate\executeCommands($commands);

if (PackageTemplate\isPagesExists())
{
    $pstatus = proc_get_status($webServerProcess);
    $pid = $pstatus['pid'];
    PackageTemplate\kill($pid);
}

echo 'Exit code: [' . $testResult . "] ";

if ($testResult == 0)
{
    echo "- All tests PASSED\n";
}
else
{
    echo "- Some tests FAIL\n";
}

PackageTemplate\updateGitignore();

exit($testResult);
