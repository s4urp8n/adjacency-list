<?php
/**
 * KEEP IN MIND THIS BOOTSTRAP FILE WILL BE COPIED TO /tests DIRECTORY
 * SO USE PATH RELATIVE TO /tests
 */

//composer
include(realpath(
    __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php'
));

include(realpath(
    __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'functions.php'
));

Zver\Autoloader::register(__DIR__ . DIRECTORY_SEPARATOR . 'classes');