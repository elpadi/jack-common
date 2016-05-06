<?php
namespace Jack;

abstract class Template {

	abstract protected static function getTemplateDir();

	public function __construct() {
		$this->loader = new \Twig_Loader_Filesystem(static::getTemplateDir());
		$this->twig = new \Twig_Environment($this->loader, array(
			'debug' => DEBUG,
			'cache' => DEBUG ? false : JACK_DIR.'/cache/twig',
		));
		$this->twig->addExtension(new \Umpirsky\Twig\Extension\PhpFunctionExtension());
		$this->twig->addFunction(new \Twig_SimpleFunction('urlFor', ['\Jack\App','routeLookUp']));
		$this->twig->addGlobal('DEBUG', DEBUG);
	}

	public function render($path, $vars) {
		try {
			$this->twig->addGlobal('MINIFY_SCRIPTS', !DEBUG);
			$this->twig->addGlobal('TEMPLATE_PATH', str_replace('/', ' ', $path));
			$this->twig->addGlobal('URL_PATH', str_replace('/', ' ', substr($_SERVER['REQUEST_URI'], strlen(PUBLIC_ROOT))));
			$content = $this->twig->render("parts/$path.twig", $vars);
		}
		catch (\Exception $e) {
			var_dump(__FILE__.":".__LINE__." - ".__METHOD__, $e);
			exit(0);
		}
		return $content;
	}

}

