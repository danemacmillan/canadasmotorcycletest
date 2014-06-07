<?php

/**
 * This app requires PHP 5.4 or later, due to use of lamdas, anonymous
 * functions, closures, and namespaces.
 *
 * Understand that this app is a stripped down version. There are no user
 * tables, and a number of advanced techniques from MVC have been watered
 * down. In addition, some filtering and checks were ignored for the sake
 * of scope.
 *
 * I chose to avoid any frameworks or CMS' for ease of installation, and to
 * demonstrate personal knowledge of PHP. The test is not supposed to be a
 * goliath piece of code.
 *
 * @author Dane MacMillan <work@danemacmillan.com>
 */

// For the purpose of this small example, the autoloader is always in the
// parent directory.
require dirname(__DIR__) . '/autoload.php';

// Track errors.
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR);
ini_set('display_errors', 0); // 1 for development.
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
