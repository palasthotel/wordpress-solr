<?php
error_reporting(E_ALL);
ini_set('display_errors', TRUE);

require_once __DIR__ . '/vendor/autoload.php';

// load configuration
if (file_exists(__DIR__ . '/config.php')) {
  require_once __DIR__ . '/config.php';
} else {
  die(
      'Configuration file missing. Please add authentication information to ' .
           '"config.sample.php" and rename it to "config.php".');
}
