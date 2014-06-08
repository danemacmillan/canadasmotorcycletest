<?php
/**
 * Quick autoloader for App. This is based off examples from PSR-4
 * documentation.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
spl_autoload_register(function ($class)
{
    // Namespace specific to this app.
    $prefix = 'CanadasMotorcycle\\';

    // Base directory for the namespace prefix
    $base_dir = __DIR__ . '/src/';

    // Check class for namespace prefix.
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // If nothing, return, so other loaders can start, if any.
        return;
    }

    $relative_class = 'class.' . strtolower(substr($class, $len));

    // Replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
