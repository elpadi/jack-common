<?php
namespace Jack;

use function Stringy\create as s;

abstract class Template {

	protected static function getTemplateDir() {}

	public static function includes($haystack, $needle) {
		switch ($htype = gettype($haystack)) {
		case 'string': return strpos($haystack, $needle) !== FALSE;
		case 'array': return in_array($needle, $haystack);
		}
		throw new \InvalidArgumentException("Cannot look in value of type '$htype'.");
	}

	protected function initTwig() {
		$loader = new \Twig_Loader_Filesystem(static::getTemplateDir());
		$twig = new \Twig_Environment($loader, array(
			'debug' => DEBUG,
			//'strict_variables' => DEBUG,
			'auto_reload' => TRUE,
			//'optimizations' => DEBUG ? 0 : -1,
			'cache' => DEBUG == FALSE
		));
		if (DEBUG && isset($_ENV['TEST_TWIG'])) {
			$deprecationsCollector = new \Twig_Util_DeprecationCollector($twig);
			$deprecations = $deprecationsCollector->collectDir(static::getTemplateDir());
			if (!empty($deprecations)) {
				dump(__FILE__.":".__LINE__." - ".__METHOD__, $deprecations);
				exit();
			}
		}
		return $twig;
	}

	protected function extendTwig(\Twig_Environment $twig) {
		global $app;
		$twig->addFunction(new \Twig_SimpleFunction('urlFor', [$app, 'routeLookUp']));
		$twig->addFunction(new \Twig_SimpleFunction('d', 'dump'));
		$twig->addFunction(new \Twig_SimpleFunction('dd', function() {
			call_user_func_array('dump', func_get_args());
			exit();
		}));
		$twig->addFilter(new \Twig_SimpleFilter('url', [$app, 'url']));
		$twig->addFilter(new \Twig_SimpleFilter('asset_url', [$app, 'assetUrl']));
		$twig->addFilter(new \Twig_SimpleFilter('append_mtime', [$app, 'appendModifiedTime']));
		$twig->addFilter(new \Twig_SimpleFilter('source', [$app, 'urlToSource']));
		$twig->addFilter(new \Twig_SimpleFilter('image_url', [$app->imageManager, 'imageUrl']));
		$twig->addFilter(new \Twig_SimpleFilter('srcset', [$app->imageManager, 'responsiveImageSrcset']));
		$twig->addFilter(new \Twig_SimpleFilter('imgsize', [$app->imageManager, 'getMediumSize']));
		$twig->addFunction(new \Twig_SimpleFunction('php', function($fn) { return call_user_func_array($fn, array_slice(func_get_args(), 1)); }));
		$twig->addFilter(new \Twig_SimpleFilter('php', function($s, $fn) { return call_user_func_array($fn, array_merge([$s], array_slice(func_get_args(), 2))); }));
		$twig->addFilter(new \Twig_SimpleFilter('pluck', '\Functional\pluck'));
		$twig->addFilter(new \Twig_SimpleFilter('ordinal_sup', function($s) { return preg_replace('/([0-9])(st|nd|rd|th)/', '$1<sup>$2</sup>', $s); }));
		$twig->addFilter(new \Twig_SimpleFilter('slug', function($s) { return s($s)->slugify(); }));
		$twig->addFilter(new \Twig_SimpleFilter('has', [__CLASS__, 'includes']));
		$twig->addFilter(new \Twig_SimpleFilter('css', [__NAMESPACE__.'\\AssetManager', 'css']));
		$twig->addFilter(new \Twig_SimpleFilter('js', [__NAMESPACE__.'\\AssetManager', 'js']));
		$twig->addGlobal('IS_LOCAL', IS_LOCAL);
		$twig->addGlobal('DEBUG', DEBUG);
		$twig->addGlobal('SERVER', $_SERVER);
	}

	public function exists($path) {
		return is_readable(static::getTemplateDir()."/$path.twig");
	}

	protected function addCommonVariables(&$vars, $path) {
		$this->twig->addGlobal('TEMPLATE_PATH', str_replace('/', ' ', $path));
		$this->twig->addGlobal('URL_PATH', str_replace('/', ' ', substr($_SERVER['REQUEST_URI'], strlen(PUBLIC_ROOT))));
	}

	public function render($path, $vars) {
		try {
			$this->twig = $this->initTwig();
			$this->extendTwig($this->twig);
			$this->addCommonVariables($vars, $path);
			$content = $this->twig->render("$path.twig", $vars);
		}
		catch (\Exception $e) {
			if (DEBUG) {
				dump(__FILE__.":".__LINE__." - ".__METHOD__, $e);
				exit(0);
			}
			return '';
		}
		return $content;
	}

	public function snippet($name, $vars=array()) {
		try {
			$twig = $this->initTwig();
			$this->extendTwig($twig);
			$path = strpos($name, '/') === FALSE ? "snippets/$name.twig" : "$name.twig";
			return $twig->render($path, $vars);
		}
		catch (\Exception $e) {
			if (DEBUG) {
				dump(__FILE__.":".__LINE__." - ".__METHOD__, $e);
				exit(0);
			}
			return '';
		}
	}

}
