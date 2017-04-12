<?php
namespace Jack;

abstract class Template {

	protected static function getTemplateDir() {}

	public static function includes($haystack, $needle) {
		switch ($htype = gettype($haystack)) {
		case 'string': return strpos($haystack, $needle) !== FALSE;
		case 'array': return in_array($needle, $haystack);
		}
		throw new \InvalidArgumentException("Cannot look in value of type '$htype'.");
	}

	public function initTwig() {
		global $app;
		$this->loader = new \Twig_Loader_Filesystem(static::getTemplateDir());
		$this->twig = new \Twig_Environment($this->loader, array(
			'debug' => DEBUG,
			'cache' => DEBUG ? false : JACK_DIR.'/cache/twig',
		));
		$this->twig->addFunction(new \Twig_SimpleFunction('php', function($fn) { return call_user_func_array($fn, array_slice(func_get_args(), 1)); }));
		$this->twig->addFunction(new \Twig_SimpleFunction('urlFor', [$app, 'routeLookUp']));
		$this->twig->addFilter(new \Twig_SimpleFilter('url', [$app, 'url']));
		$this->twig->addFilter(new \Twig_SimpleFilter('asset_url', [$app, 'assetUrl']));
		$this->twig->addFilter(new \Twig_SimpleFilter('source', [$app, 'urlToSource']));
		$this->twig->addFilter(new \Twig_SimpleFilter('image_url', [$app->imageManager, 'imageUrl']));
		$this->twig->addFilter(new \Twig_SimpleFilter('srcset', [$app->imageManager, 'responsiveImageSrcset']));
		$this->twig->addFilter(new \Twig_SimpleFilter('pluck', '\Functional\pluck'));
		$this->twig->addFilter(new \Twig_SimpleFilter('ordinal_sup', function($s) { return preg_replace('/([0-9])(st|nd|rd|th)/', '$1<sup>$2</sup>', $s); }));
		$this->twig->addFilter(new \Twig_SimpleFilter('slug', function($s) { return trim(preg_replace('/[^a-z0-9]/', '-', trim(str_replace('&', 'and', strtolower($s)))), '-'); }));
		$this->twig->addFilter(new \Twig_SimpleFilter('has', [__CLASS__, 'includes']));
		$this->twig->addFilter(new \Twig_SimpleFilter('css', [__NAMESPACE__.'\\AssetManager', 'css']));
		$this->twig->addFilter(new \Twig_SimpleFilter('js', [__NAMESPACE__.'\\AssetManager', 'js']));
		$this->twig->addGlobal('DEBUG', DEBUG);
		$this->twig->addGlobal('SERVER', $_SERVER);
	}

	public function exists($path) {
		return is_readable(static::getTemplateDir()."/$path.twig");
	}

	public function render($path, $vars) {
		try {
			$this->initTwig();
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

}

