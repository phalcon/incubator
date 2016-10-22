# Phalcon\CssJs

The CssJSManager is an object te centrally manage used CSS files and JS files an to merge and compress 
them as required.

To use this class do the following:

Add an CssJSManger section to your Phalcon\Config in your DI.
```
    'CssJsManager' => [
        'publicDir' => BASE_PATH . '/public/',
        'merge' => true,
        'cache' => true,
        'controllerlevel' => false,
        'preloadCss' => ['http://netdna.bootstrapcdn.com/bootswatch/2.3.1/united/bootstrap.min.css',
            'css/style.css',
            'css/sb-admin-2.min.css',
        ],
        'preloadJs' => [
            'http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js',
            'http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/js/bootstrap.min.js',
            'css/sb-admin-2.min.js',
        ],
    ],

```
CSS files and JS files that are required in every request can be added in the preloadcss and
preloadjs array's. Controller specific files can be added in the controller.

Add the CssJSManger as a service to your DI.
```
/**
 * Add the shared CSS and JS files manager to the services.
 */
$di->setShared('CssJSManager', function() {
    $config = $this->getConfig();
    $cssJsManger = new CssJsManager();
    $cssJsManger->setMergeFiles($config->CssJsManager->merge);
    $cssJsManger->setUseCache($config->CssJsManager->cache);
    $cssJsManger->addCss($config->CssJsManager->preloadCss);
    $cssJsManger->addJs($config->CssJsManager->preloadJs);
    return $cssJsManger;
});

```

In your BaseController add (or extend) the following eventhandler.
```
    public function afterExecuteRoute(Dispatcher $dispatcher) {
        $view = $this->di->getShared('view');
        $cssJsManger = $this->di->getShared('CssJSManager');
        $config = $this->di->getConfig();
        if ($config->CssJsManager->controllerlevel) {
            $fn = $dispatcher->getControllerName();
        }
        else {
            $fn = $config->application->name;
        }
        $view->js = $cssJsManger->jsAsHtml ($fn);
        $view->css = $cssJsManger->cssAsHtml ($fn);
    }

```
This event handler makes te variables js and css available to your view. 

Update your view(s) like below.
```
<!DOCTYPE html>
<html>
	<head>
		<title>CssJSExample</title>
        <!-- add css in the head section -->
        {{ css }}
        
	</head>
	<body>
        
		{{ content() }}

        <!-- add js prefferably at the end of the body -->
        {{ js }}

	</body>
</html>
```

In your controllers add when needed a CSS or JS resource to the manager like:
```
    $this->di->getShared('CssJSManager')->addCss('css/mycss.css');
    $this->di->getShared('CssJSManager')->addJs('css/mycss.css');

```