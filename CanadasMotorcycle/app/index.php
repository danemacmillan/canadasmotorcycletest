<?php

/**
 * This app requires PHP 5.4 or later, due to use of lamdas, anonymous
 * functions, and namespaces.
 */

// For the purpose of this small example, the autoloader is always in the
// parent directory.
require dirname(__DIR__) . '/autoload.php';

//print_r(scandir(dirname(__DIR__).'/app'));

// Track errors.
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR);
ini_set('display_errors', 1);
ini_set('track_errors', 1);

// Define the database user and password.
define('DB_NAME', 'canadas_motorcycle');
define('DB_USER', 'root');
define('DB_PASSWORD', '');

// There is only one user for this test. Developing an authentication layer
// is way beyond the scope of this test.
define('USER_ID', 1);

// Import the App class.
use CanadasMotorcycle\App;

// Instantiate the app and start it.
$app = new App();
$app->start();
