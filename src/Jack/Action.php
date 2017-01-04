<?php
namespace Jack;

class Action {

	public static function create($route, $group) {
		if (isset($route['action']) && is_string($route['action'])) return new Action\Redirect($route);
		$actionName = str_replace(' ', '', ucwords(preg_replace('/[^a-z]/', ' ', $route['name'])));
		foreach (App::namespaces() as $ns) {
			$classname = '\\'.$ns.'\Action\\'.(isset($group['name']) ? "$group[name]\\" : '').$actionName;
			if (class_exists($classname)) return new $classname($route);
		}
		foreach (App::namespaces() as $ns) {
			$classname = '\\'.$ns.'\Action\Page';
			if (class_exists($classname)) return new $classname($route);
		}
		return new Action\NotFound();
	}

}
