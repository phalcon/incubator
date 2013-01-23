Phalcon\Mvc\View\Engine
=======================

Adapters to use other template engines with Phalcon

Mustache
--------
[Mustache](https://github.com/bobthecow/mustache.php) is a logic-less template engine available
for many platforms and languages. A PHP implementation is available in
[this Github repository](https://github.com/bobthecow/mustache.php).

You need to manually load the Mustache library before use its engine adapter. Either registering
an autoload function or including the relevant file first can achieve this.

```php
require "path/to/Mustache/Autoloader.php";
Mustache_Autoloader::register();
```

Register the adapter in the view component:

```php
//Setting up the view component
$di->set('view', function() {

    $view = new \Phalcon\Mvc\View();

    $view->setViewsDir('../app/views/');

    $view->registerEngines(
		array(".mhtml" => 'Phalcon\Mvc\View\Engine\Mustache')
	);

    return $view;
});
```

Twig
----
[Twig](http://twig.sensiolabs.org/) is a modern template engine for PHP.

You need to manually load the Twig library before use its engine adapter. Registering its autoloader could do this:

```php
require "path/to/Twig/Autoloader.php";
Twig_Autoloader::register();
```
Register the adapter in the view component:

```php
//Setting up the view component
$di->set('view', function() {

    $view = new \Phalcon\Mvc\View();

    $view->setViewsDir('../app/views/');

    $view->registerEngines(
		array(".twig" => 'Phalcon\Mvc\View\Engine\Twig')
	);

    return $view;
});
```

Smarty
------
[Smarty](http://www.smarty.net/) is a template engine for PHP, facilitating the separation of presentation
(HTML/CSS) from application logic.

You need to manually include the Smarty library before use its engine adapter. Including its adapter:

```php
require_once 'Smarty3/Smarty.class.php';
```

Register the adapter in the view component:

```php
//Setting up the view component
$di->set('view', function() {

    $view = new \Phalcon\Mvc\View();

    $view->setViewsDir('../app/views/');

    $view->registerEngines(
		array(".tpl" => 'Phalcon\Mvc\View\Engine\Smarty')
	);

    return $view;
});
```

Smarty can be configured to alter its default behavior, the following example explain how to do that:

```php
$di->set('view', function() use ($config) {

	$view = new \Phalcon\Mvc\View();
	$view->setViewsDir('../app/views/');
	
	$view->registerEngines(
		array('.html' => function($view, $di) {
		
				$smarty = new \Phalcon\Mvc\View\Engine\Smarty($view, $di);

				$smarty->setOptions(array(
					'template_dir'		=> $view->getViewsDir(),
					'compile_dir'		=> '../app/viewscompiled',
					'error_reporting'	=> error_reporting() ^ E_NOTICE,
					'escape_html'		=> true,
					'_file_perms'		=> 0666,
					'_dir_perms'		=> 0777,
					'force_compile'		=> false,
					'compile_check'		=> true,
					'caching'			=> false,
					'debugging'			=> true,
				));

				return $smarty;
			}
		)
	);

	return $view;
});
```
