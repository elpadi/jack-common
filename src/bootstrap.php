<?php
use Jack\Jack;
use Noodlehaus\Config;

define('JACK_DIR', dirname(__DIR__));
define('IS_LOCAL', in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', "::1")) || strpos($_SERVER['HTTP_HOST'], 'localhost') !== false);

require(JACK_DIR.'/vendor/autoload.php');
require(JACK_DIR.'/config/propel/propel.php');
Jack::$config = new Config(JACK_DIR.'/config/jack');

defined('DEBUG') or define('DEBUG', IS_LOCAL || Jack::$config->get('debug'));
function s($s) { return new Spatie\String\Str($s); }

ini_set('display_errors', DEBUG ? 'on' : 'off');
error_reporting(DEBUG ? E_ERROR | E_WARNING | E_PARSE | E_NOTICE : 0);
