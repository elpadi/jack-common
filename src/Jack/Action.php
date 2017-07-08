<?php
namespace Jack;

use Symfony\Component\EventDispatcher\GenericEvent;
use Functional as F;

class Action {

	public static function routeNameToActionName($s) {
		$parts = preg_split('/[^a-z]/', $s);
		return $parts[0].join('', F\map(F\tail($parts), function($s) { return ucwords($s); }));
	}

	public static function create($route, $group, $routeArgs) {
		if (isset($route['action']) && is_string($route['action'])) return new Action\Redirect($route);
		$actionName = static::routeNameToActionName($route['name']);
		if (isset($group['name'])) {
			$possibilities[] = ['class', ucfirst($group['name']), ucfirst($actionName)];
			$possibilities[] = ['method', [ucfirst($group['name'])], $actionName];
		}
		$possibilities[] = ['class', 'Pages', ucfirst($actionName)];
		$possibilities[] = ['method', ['Page'], $actionName];
		$possibilities[] = ['class', 'Page'];
		$event = new GenericEvent(NULL, compact('possibilities','route','routeArgs'));
		App::$container['events']->dispatch('action.edit_possibilities', $event);
		$match = F\first(F\select(F\map($event['possibilities'], function($p) use ($route) {
			if ($p[0] === 'class') return static::matchClass(F\tail($p), $route);
			if ($p[0] === 'method') return static::matchMethod($p[1], $p[2], $route);
		}), '\\Functional\\id'));
		return $match ? $match : new Action\NotFound();
	}

	protected static function matchClass($parts, $route) {
		foreach (App::namespaces() as $ns) {
			$class = join('\\', array_merge(['', $ns, 'Action'], $parts));
			if (class_exists($class)) return new $class($route);
		}
		return false;
	}

	protected static function matchMethod($class_parts, $method, $route) {
		foreach (App::namespaces() as $ns) {
			$class = join('\\', array_merge(['', $ns, 'Action'], $class_parts));
			if (
				class_exists($class)
				&& method_exists(new $class($route), $method)
			) {
				return new $class($route, $method);
			}
		}
		return false;
	}

}
