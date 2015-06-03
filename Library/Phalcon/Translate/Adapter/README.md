Phalcon\Translate\Adapter
=========================

Usage examples of the adapters available here:

Database
--------
You can use your database to store the translations, too.

First of all, you need to up your database. To do this, use [DI](http://docs.phalconphp.com/en/latest/api/Phalcon_DI.html) (in `/public/index.php`). Take a look:
```php
// ...

$di->set('db', function() {
	return new \Phalcon\Db\Adapter\Pdo\Mysql([
		'host' => 'localhost',
		'username' => 'root',
		'password' => 123456,
		'dbname' => 'application'
	]);
});

// ...
```

Then, you should get the translation through your `controller`. Put this on it:
```php
<?php

class IndexController extends \Phalcon\Mvc\Controller
{
	protected function _getTranslation()
	{
		return new Phalcon\Translate\Adapter\Database([
		    'db' => $this->di->get('db'), // Here we're getting the database from DI
		    'table' => 'translations', // The table that is storing the translations
		    'language' => $this->request->getBestLanguage() // Now we're getting the best language for the user
		]);
	}
	
	// ...
}
```

To store the translations, the following table is recommended:
```sql
CREATE TABLE `translations` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `language` VARCHAR(5) NOT NULL COLLATE 'utf8_bin',
    `key_name` VARCHAR(48) NOT NULL COLLATE 'utf8_bin',
    `value` TEXT NOT NULL COLLATE 'utf8_bin',
    PRIMARY KEY (`id`)
)
```

The columns are self-described, but pay attention to `language` — it's a column that stores the language that the user is using, that can be `en`, `en-us` or `en-US`. Now it's your responsibility to decide which pattern you want to use.

To display for your users the translated words you need to set up a variable to store the expressions/translations from your database. *This step happens in your controller.* Follow the example:
```php
<?php

class IndexController extends \Phalcon\Mvc\Controller
{
	protected function _getTranslation()
	{
		// ...
	}
	
	public function indexAction()
	{
		$this->view->setVar('expression', $this->_getTranslation());
	}
}
```

Then, just output the phrase/sentence/word in your view:
```html+php
<html>
	<head>
		<!-- ... -->
	</head>
	<body>
		<h1><?php echo $expression->_("IndexPage_Hello_World"); ?></h1>
	</body>
</html>
```

Or, if you wish you can use [Volt](http://docs.phalconphp.com/en/latest/reference/volt.html):
```html+php
<h1>{{ expression._("IndexPage_Hello_World") }}</h1>
```

CSV
--------
This adapter uses CSV as translation frontend.

```php
$translate = new Phalcon\Translate\Adapter\Csv([
    'file' => 'fr_FR.csv', // required
    'delimiter' => ',', // optional, default - ;
    'length' => '4096', // optional, default - 0
    'enclosure' => '^', // optional, default - "
]);

echo $translate->_('Hello');
echo $translate->_('My name is %name%', array('name' => 'John Doe')); //Je m'appelle John Doe
```

ResourceBundle
--------------
This adapter uses ResourceBundle as translation frontend.

The extension [intl](http://php.net/manual/en/book.intl.php) must be installed in PHP.

```php
$translate = new Phalcon\Translate\Adapter\ResourceBundle([
    'bundle'    => '/path/to/bundle', // required
    'locale'    => 'en',              // required
    'fallback'  => false              // optional, default - true
]);

echo $translate->t('application.title');
echo $translate->t('application.copyright', ['currentYear' => new \DateTime('now')]);
```

ResourceBundle source file example

```
root {
    application {
        title { "Hello world" }
        copyright { "&copy; 2001-{currentYear, date, Y}. Foobar" }
    }
}
```
