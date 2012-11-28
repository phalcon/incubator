
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

