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
			'cache' => DEBUG ? false : JACK_DIR.'/cache/twig',
		));
		return $twig;
	}

	protected function extendTwig(\Twig_Environment $twig) {
		global $app;
		$twig->addFunction(new \Twig_SimpleFunction('urlFor', [$app, 'routeLookUp']));
		$twig->addFilter(new \Twig_SimpleFilter('url', [$app, 'url']));
		$twig->addFilter(new \Twig_SimpleFilter('asset_url', [$app, 'assetUrl']));
		$twig->addFilter(new \Twig_SimpleFilter('source', [$app, 'urlToSource']));
		$twig->addFilter(new \Twig_SimpleFilter('image_url', [$app->imageManager, 'imageUrl']));
		$twig->addFilter(new \Twig_SimpleFilter('srcset', [$app->imageManager, 'responsiveImageSrcset']));
		$twig->addFunction(new \Twig_SimpleFunction('php', function($fn) { return call_user_func_array($fn, array_slice(func_get_args(), 1)); }));
		$twig->addFilter(new \Twig_SimpleFilter('php', function($s, $fn) { return call_user_func_array($fn, array_merge([$s], array_slice(func_get_args(), 2))); }));
		$twig->addFilter(new \Twig_SimpleFilter('pluck', '\Functional\pluck'));
		$twig->addFilter(new \Twig_SimpleFilter('ordinal_sup', function($s) { return preg_replace('/([0-9])(st|nd|rd|th)/', '$1<sup>$2</sup>', $s); }));
		$twig->addFilter(new \Twig_SimpleFilter('slug', function($s) { return s($s)->slugify(); }));
		$twig->addFilter(new \Twig_SimpleFilter('has', [__CLASS__, 'includes']));
		$twig->addFilter(new \Twig_SimpleFilter('css', [__NAMESPACE__.'\\AssetManager', 'css']));
		$twig->addFilter(new \Twig_SimpleFilter('js', [__NAMESPACE__.'\\AssetManager', 'js']));
		$twig->addGlobal('DEBUG', DEBUG);
		$twig->addGlobal('SERVER', $_SERVER);
	}

	public function exists($path) {
		return is_readable(static::getTemplateDir()."/$path.twig");
	}

	public function render($path, $vars) {
		try {
			$this->twig = $this->initTwig();
			$this->extendTwig($this->twig);
			$this->twig->addGlobal('TEMPLATE_PATH', str_replace('/', ' ', $path));
			$this->twig->addGlobal('URL_PATH', str_replace('/', ' ', substr($_SERVER['REQUEST_URI'], strlen(PUBLIC_ROOT))));
			$content = $this->twig->render("$path.twig", $vars);
		}
		catch (\Exception $e) {
			var_dump(__FILE__.":".__LINE__." - ".__METHOD__, $e);
			exit(0);
		}
		return $content;
	}

	public function snippet($name, $vars=array()) {
		$twig = $this->initTwig();
		$this->extendTwig($twig);
		$path = strpos($name, '/') === FALSE ? "snippets/$name.twig" : "$name.twig";
		return $twig->render($path, $vars);
	}

}
