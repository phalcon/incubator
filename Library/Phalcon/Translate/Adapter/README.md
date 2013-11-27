
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

Database
--------
This adapter uses a table to store the translation messages:

```php
$connection = new \Phalcon\Db\Adapter\Pdo\Mysql(array(
    "host" => "localhost",
    "username" => "root",
    "password" => "secret",
    "dbname" => "test"
));

$translate = new Phalcon\Translate\Adapter\Database(array(
    'db' => $connection,
    'table' => 'translations'
));
```

The following table is required to store the translations:

```php
CREATE TABLE `translations` (
  `key_name` varchar(32) NOT NULL,
  `value` text,
  PRIMARY KEY (`key_name`)
)
```

```php
echo $translate->_('Hello');
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