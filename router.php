<?php
/**
 * This is router of php built-in webserver
 */

$pages = scandir(__DIR__ . DIRECTORY_SEPARATOR . 'pages');

array_shift($pages);//.
array_shift($pages);//..

$pages = array_map(
    function ($value)
    {
        return '/' . $value;
    }, $pages
);

$c3Directory = __DIR__ . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . '_output' . DIRECTORY_SEPARATOR . 'c3tmp'
               . DIRECTORY_SEPARATOR;

if (in_array($_SERVER['REQUEST_URI'], $pages))
{
    $page = mb_eregi_replace('/', '', $_SERVER['REQUEST_URI']);
    include __DIR__ . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . $page;
}
else
{
    return false;
}
?>