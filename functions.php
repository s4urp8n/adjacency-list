<?php

namespace PackageTemplate
{
    
    include 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
    
    use Zver\ArrayHelper;
    use Zver\StringHelper;
    
    function updateGitignore()
    {
        /**
         * Codeception tests/_output fix
         */
        $gitignore = preg_replace('/^tests[^\s]+\s*/im', '', file_get_contents('.gitignore'));
        $gitignore = preg_replace('/^\s*$/im', '', $gitignore);
        file_put_contents('.gitignore', $gitignore, LOCK_EX);
        
    }
    
    function testFile($path)
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR;
        
        return StringHelper::load($path)
                           ->removeBeginning('/')
                           ->replace('/', DIRECTORY_SEPARATOR)
                           ->prepend($dir)
                           ->get();
    }
    
    function getDocIndexUrl()
    {
        $origin = StringHelper::load(getGitOrigin());
        
        $repo = $origin->getClone()
                       ->getLastPart('/')
                       ->getFirstPart('.')
                       ->get();
        
        $user = $origin->getClone()
                       ->getParts(3, '/', '')
                       ->get();
        
        $url = "https://" . $user . '.github.io/' . $repo . '/index.html';
        
        return '[Documentation](' . $url . ')';
    }
    
    function getGitOrigin()
    {
        return ArrayHelper::load(
            StringHelper::load(shell_exec("git remote show origin"))
                        ->toLinesArray()
        )
                          ->map(
                              function ($key, $value)
                              {
                                  return StringHelper::load($value)
                                                     ->trimSpaces()
                                                     ->toLowerCase()
                                                     ->get();
                              }
                          )
                          ->filter(
                              function ($key, $value)
                              {
                                  return StringHelper::load($value)
                                                     ->isStartsWith('fetch url: ');
                              }
                          )
                          ->map(
                              function ($key, $value)
                              {
                                  return StringHelper::load($value)
                                                     ->substring(11)
                                                     ->get();
                              }
                          )
                          ->getFirstValue();
    }
    
    function updateReadme($config)
    {
        $origin = getGitOrigin();
        
        echo "Updating README...";
        chdir(__DIR__);
        
        $readme = $config['readme'];
        
        $codeCoverage = file_get_contents('tests/_output/coverage.txt');
        $codeCoverage = '```' . $codeCoverage . '```';
        
        $readme = mb_eregi_replace('{{COVERAGE_HERE}}', $codeCoverage, $readme);
        $readme = mb_eregi_replace('{{DOC_URL_HERE}}', getDocIndexUrl(), $readme);
        
        file_put_contents('README.md', $readme, LOCK_EX);
        
        echo "\n";
    }
    
    function isPagesExists()
    {
        return file_exists('package' . DIRECTORY_SEPARATOR . 'pages');
    }
    
    function page($page)
    {
        return "http://127.0.0.1:4444/" . $page . '.php';
    }
    
    function getRoot()
    {
        return __DIR__ . DIRECTORY_SEPARATOR;
    }
    
    function kill($pid)
    {
        return stripos(php_uname('s'), 'win') > -1
            ? exec("taskkill /F /T /PID $pid")
            : exec("kill -9 $pid");
    }
    
    function forceUnlink($path)
    {
        return stripos(php_uname('s'), 'win') > -1
            ? exec('del /F /Q "' . $path . '"')
            : exec('rm -f "' . $path . '"');
    }
    
    function forceRmdir($path)
    {
        return stripos(php_uname('s'), 'win') > -1
            ? exec('rmdir /S /Q "' . $path . '"')
            : exec('rm -rf "' . $path . '"');
    }
    
    function copyDirectory($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir)))
        {
            if (($file != '.') && ($file != '..'))
            {
                if (is_dir($src . '/' . $file))
                {
                    copyDirectory($src . '/' . $file, $dst . '/' . $file);
                }
                else
                {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
    
    function downloadFile($link, $file = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_REFERER, $link);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($curl);
        curl_close($curl);
        
        file_put_contents(
            is_null($file)
                ? basename($link)
                : $file, $content, LOCK_EX
        );
    }
    
    function executeCommands($commands)
    {
        $comandsCount = count($commands);
        
        for ($i = 0; $i < $comandsCount; $i++)
        {
            if ($i == 0)
            {
                echo "\n\n";
            }
            if (!empty($commands[$i]['description']))
            {
                echo $commands[$i]['description'] . "\n\n";
            }
            
            if (!empty($commands[$i]['command']))
            {
                echo exec($commands[$i]['command']) . "\n\n";
            }
            
            if (!empty($commands[$i]['callback']))
            {
                call_user_func(($commands[$i]['callback']));
            }
            if ($i == $comandsCount - 1)
            {
                echo "\n\n";
            }
        }
    }
    
    function removePath($path, $callback = null)
    {
        if (file_exists($path))
        {
            
            if (is_file($path))
            {
                if (is_null($callback) || (is_callable($callback) && $callback($path) === true))
                {
                    forceUnlink($path);
                }
            }
            else
            {
                
                $iterator = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
                $files = new \RecursiveIteratorIterator(
                    $iterator, \RecursiveIteratorIterator::CHILD_FIRST
                );
                
                foreach ($files as $file)
                {
                    if ($file->isDir())
                    {
                        if (is_null($callback) || (is_callable($callback) && $callback($file->getRealPath()) === true))
                        {
                            forceRmdir($file->getRealPath());
                        }
                    }
                    else
                    {
                        if (is_null($callback) || (is_callable($callback) && $callback($file->getRealPath()) === true))
                        {
                            forceUnlink($file->getRealPath());
                        }
                    }
                }
                if (is_null($callback) || (is_callable($callback) && $callback($path) === true))
                {
                    forceRmdir($path);
                }
            }
            
        }
    }
    
}
