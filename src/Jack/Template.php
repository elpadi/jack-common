<?php
namespace Jack;

abstract class Template {

	abstract protected static function getTemplateDir();

	public static function includes($haystack, $needle) {
		switch ($htype = gettype($haystack)) {
		case 'string': return strpos($haystack, $needle) !== FALSE;
		case 'array': return in_array($needle, $haystack);
		}
		throw new \InvalidArgumentException("Cannot look in value of type '$htype'.");
	}

	public function __construct() {
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
		$this->twig->addFilter(new \Twig_SimpleFilter('image_url', [$app, 'imageUrl']));
		$this->twig->addFilter(new \Twig_SimpleFilter('srcset', function($s) { return implode(', ', Images::responsiveImageSrcset($s)); }));
		$this->twig->addFilter(new \Twig_SimpleFilter('pluck', '\Functional\pluck'));
		$this->twig->addFilter(new \Twig_SimpleFilter('ordinal_sup', function($s) { return preg_replace('/([0-9])(st|nd|rd|th)/', '$1<sup>$2</sup>', $s); }));
		$this->twig->addFilter(new \Twig_SimpleFilter('slug', function($s) { return trim(preg_replace('/[^a-z0-9]/', '-', trim(strtolower($s))), '-'); }));
		$this->twig->addFilter(new \Twig_SimpleFilter('has', [__CLASS__, 'includes']));
		$this->twig->addGlobal('DEBUG', DEBUG);
	}

	public function render($path, $vars) {
		try {
			$this->twig->addGlobal('MINIFY_SCRIPTS', !DEBUG);
			$this->twig->addGlobal('TEMPLATE_PATH', str_replace('/', ' ', $path));
			$this->twig->addGlobal('URL_PATH', str_replace('/', ' ', substr($_SERVER['REQUEST_URI'], strlen(PUBLIC_ROOT))));
			$content = $this->twig->render($path === 'default' ? "$path.twig" : "parts/$path.twig", $vars);
		}
		catch (\Exception $e) {
			var_dump(__FILE__.":".__LINE__." - ".__METHOD__, $e);
			exit(0);
		}
		return $content;
	}

}

