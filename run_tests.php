<?php
/**
 * Simple test runner script
 */

// Change to project directory
chdir(__DIR__);

// Include Composer autoloader
require 'vendor/autoload.php';

// Run tests via PHPUnit
$testFile = $argv[1] ?? 'tests/Unit/Models/Tenant/StudentTest.php';

// Use system call to run phpunit
system('vendor/bin/phpunit ' . escapeshellarg($testFile));
?>
