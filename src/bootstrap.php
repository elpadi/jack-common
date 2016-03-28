<?php
use Jack\Jack;
use Noodlehaus\Config;

define('JACK_DIR', dirname(__DIR__));
define('IS_LOCAL', false);

require(JACK_DIR.'/vendor/autoload.php');
require(JACK_DIR.'/config/propel/propel.php');
Jack::$config = new Config(JACK_DIR.'/config/jack');

define('DEBUG', false);
function s($s) { return new Spatie\String\Str($s); }

ini_set('display_errors', DEBUG ? 'on' : 'off');
error_reporting(DEBUG ? E_ERROR | E_WARNING | E_PARSE | E_NOTICE : 0);
