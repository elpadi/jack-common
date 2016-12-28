<?php
use Jack\Jack;

define('JACK_DIR', dirname(__DIR__));
define('IS_LOCAL', in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', "::1")) || strpos($_SERVER['HTTP_HOST'], 'localhost') !== false);
defined('DEBUG') or define('DEBUG', isset($_ENV['debug']) ? ($_ENV['debug'] === 'true') : IS_LOCAL);

ini_set('log_errors','on');
ini_set('error_log', JACK_DIR.'/log/php_errors.log');
ini_set('display_errors', DEBUG ? 'on' : 'off');
error_reporting(DEBUG ? E_ALL : 0);

require(JACK_DIR.'/vendor/autoload.php');

