Phalcon\Translate\Adapter
=========================

Usage examples of the adapters available here:

Gettext
-------
This adapter uses gettext as translation frontend.

The extension [gettext](http://www.php.net/manual/en/book.gettext.php) must be installed in PHP.

Let's pretend your application have the following translation structure:

```bash
app/
  lang/
     en_US/
         LC_MESSAGES/
             messages.po
             messages.mo
     fr_FR/
         LC_MESSAGES/
             messages.po
             messages.mo
```

A translation file (fr_FR/LC_MESSAGES/messages.po) contains these definitions:

```gettext
msgid "Hello"
msgstr "Bonjour"

msgid "My name is %name%"
msgstr "Je m'appelle %name%"
```

A .po file is compiled using msgfmt:

```bash
msgfmt -o messages.mo messages.po
```

It may be necessary to restart the web server after compile the .po files

The adapter can be used as follows:

```php
$translate = new Phalcon\Translate\Adapter\Gettext(array(
	'locale' => 'fr_FR',
	'file' => 'messages',
	'directory' => '../app/lang'
));
```

```php
echo $translate->_('Hello'); //Bonjour
echo $translate->_('My name is %name%', array('name' => 'Peter')); //Je m'appelle Peter
```

### Translation contexts

Use the __() (alias to cquery()) method if you have multiple translations of a string in different contexts:

```gettext
msgid "Hello"
msgstr "Bonjour"

msgctxt "informal"
msgid "Hello"
msgstr "Salut"

msgctxt "evening"
msgid "Hello"
msgstr "Bonsoir"

msgid "Hello %name%"
msgstr "Salut %name%"
```

```php
echo $translate->_('Hello');                  //Bonjour
echo $translate->__('Hello');                 //Bonjour
echo $translate->__('Hello', 'informal');     //Salut
echo $translate->__('Hello', 'evening');      //Bonsoir
echo $translate->cquery('Hello', 'evening');  //Bonsoir
// placeholders are supported as well
echo $translate->__('Hello %name%', NULL, array('name' => 'Bob'));   //Salut Bob
```

### Translation domains

Multiple translations domains are supported by the dquery() method. Let's say you have two files with translations:

```gettext
# frontend.po
msgid "Hello"
msgstr "Hello, visitor"
```
Additionally, you have a file named *backend.po*:

```gettext
# backend.po
msgid "Hello"
msgstr "Hello, admin"

msgctxt "evening"
msgid "Hello"
msgstr "Bonsoir, admin"

msgid "Hello %name%"
msgstr "Salut %name%"
```

```php
echo $translate->dquery('frontend', 'Hello');             //Hello, visitor
echo $translate->dquery('backend', 'Hello');              //Hello, admin
// contexts supported
echo $translate->dquery('backend', 'Hello', 'evening');   //Bonsoir, admin
// placeholders are supported as well
echo $translate->dquery('backend', 'Hello %name%', NULL, array('name' => 'Bob'));   //Salut Bob
```

### Multiple plural forms

Some languages require multiple plural forms of nouns depending on the object count. In gettext catalogs, plural forms need to be specified as such:

```gettext
"Plural-Forms: nplurals=3; plural=n>4 ? 2 : n>1 ? 1 : 0;\n"

msgid "banana"
msgid_plural "bananas"
msgstr[0] "banán"
msgstr[1] "banány"
msgstr[2] "banánov"
```

We can then leverage the multi-plural form support offered by the Gettext adapter:

```php
for ($i = 1; $i < 7; $i++) {
    echo "I have $i " .  $translate->nquery('banana', 'bananas', $i);
}
// 1 banán
// 2 banány
// 3 banány
// 4 banány
// 5 banánov
// 6 banánov
```

Method cnquery() is a plural-form counterpart to cquery().

```php
(string) public function cnquery($msgid1, $msgid2, $count, $msgctxt = null, $placeholders = null, $category = LC_MESSAGES, $domain = null)
```

Method dnquery() is a plural-form counterpart to dquery().

```php
(string) public function dnquery($domain, $msgid1, $msgid2, $count, $msgctxt = null, $placeholders = null, $category = LC_MESSAGES)
```

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
