Phalcon\Utils\Text
=============
This *static* class is responsible to help you to deal with words, texts, sentences and phrases.

Slugify
---
Creates a slug for the passed string taking into account international characters.

### Requeriments
The extension [iconv](http://php.net/manual/en/book.iconv.php) must be installed in PHP.

### Examples
```php
use \Phalcon\Utils\Text as Text;

// Output: messd-up-text-just-to-stress-test-our-little-clean-url-function
echo Text::slugify
        ("Mess'd up --text-- just (to) stress /test/ ?our! `little` \\clean\\ url fun.ction!?-->");

// The following expression will echo: perche-l-erba-e-verde
echo Text::slugify("Perché l'erba è verde?", "'");
```

Pluralize
---
Returns a pluralized word (or not) based on an array.

### Examples
```php
use \Phalcon\Utils\Text as Text;

// A simple array with three values.
$consoles = ['Xbox One', 'Playstation 4', 'WiiU'];

// Output: "There is/are 3 consoles available."
echo 'There is/are ' . count($consoles) . ' ' . Text::pluralize($consoles, 'console') . ' available.';
```

```php
// Another simple array with one value.
$todaysPosts = ['Nelson Mandela dies at 95'];

// Output: "Found 1 post today."
echo 'Found ' . count($postsForToday) . ' ' . Text::pluralize($todaysPosts, 'post') . ' today.';
```

Integrating with Volt
---
To integrate with [Volt Template Engine](http://docs.phalconphp.com/en/latest/reference/volt.html) you just need to add the function that you want to use in your DI.

To do this, go to your Volt's DI (probably in `/public/index.php`), and just add a function to the engine. See:

```php
// This file is "/public/index.php", where I manage all DIs.

// ...
    $di->set('voltService', function($view, $di) {
        $volt = new Volt($view, $di);
        
        // your implementation goes here...
        
        $compiler = $volt->getCompiler();
        
        $compiler->addFunction('pluralize', function($resolvedArgs, $exprArga) {
            return 'Phalcon\Utils\Text::pluralize(' . $resolvedArgs . ')';
        });
        
        // more of yours' implementation...
        
        return $volt;
    });
// ...
```

Then, in your view:

```html+php
<html>
    <head><!-- Your application's head here --></head>
    <body>
        There is/are 
        {{ consolesQuantity }} {{ pluralize(['Xbox One', 'Playstation 4'], 'console') }}  
        available.
    </body>
</html>
```

To learn more about Volt's functions, [just read the documentation](http://docs.phalconphp.com/en/latest/reference/volt.html#functions).

***
Credits
----
* Matteo Spinelli (http://cubiq.org) [php-clean-url-generator](http://cubiq.org/the-perfect-php-clean-url-generator)
* Guilherme Oderdenge ([email](mailto:guilhermeoderdenge@gmail.com), [twitter](http://twitter.com/chiefgui) & [site](http://vincae.com))
