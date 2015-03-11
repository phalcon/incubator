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

or

```php
//Setting up the view component
$di->set('view', function() {

    $view = new \Phalcon\Mvc\View();

    $view->setViewsDir('../app/views/');

    $view->registerEngines(
        array(
            '.twig' => function($view, $di) {
                //Setting up Twig Environment Options
                $options = array('cache' => '../cache/');
                $twig = new \Phalcon\Mvc\View\Engine\Twig($view, $di, $options);
                return $twig;
            }));

    return $view;
});
```

You can also create your own defined functions to extend Twig parsing capabilities by passing a forth parameter to the Twig constructor that consists of an Array of Twig_SimpleFunction elements. This will allow you to extend Twig, or even override default functions, with your own.

```php
//Setting up the view component
$di->set('view', function() {

    $view = new \Phalcon\Mvc\View();

    $view->setViewsDir('../app/views/');

    $view->registerEngines(
        array(
            '.twig' => function($view, $di) {
                //Setting up Twig Environment Options
                $options = array('cache' => '../cache/');
                // Adding support for the native PHP chunk_split function
                $user_functions = array(
		            new \Twig_SimpleFunction('chunk_split', function ($string, $len = 76, $end = "\r\n") use ($di) {
		                return chunk_split($string, $len, $end);
		            }, $options)
		        );
                $twig = new \Phalcon\Mvc\View\Engine\Twig($view, $di, $options, $user_functions);
                return $twig;
            }));

    return $view;
});
```

The engine implements "assets" tag in Twig templates:

```django
<!DOCTYPE html>
<html>
<head>
    <title>Project name</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    {% assets addCss('//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.min.css', false) %}
    {% assets addCss('css/style.css') %}
    {% block extraStyles %}{% endblock %}
    {{ assetsOutputCss() }}
</head>
<body>
    <div class="container_12">
        {% block content %}{% endblock %}
    </div>

    {% assets addJs('js/jquery.js') %}
    {% block extraScripts %}{% endblock %}
    {{ assetsOutputJs() }}
</body>
</html>
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
