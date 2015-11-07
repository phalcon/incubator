# Phalcon\Utils

Utility functions:

## Slug

Creates a slug for the passed string taking into account international characters.

### Examples

```php
use Phalcon\Utils\Slug as PhSlug;

echo PhSlug::generate("Mess'd up --text-- just (to) stress /test/ ?our! `little` \\clean\\ url fun.ction!?-->");
// returns: messd-up-text-just-to-stress-test-our-little-clean-url-function

echo PhSlug::generate("Perché l'erba è verde?", "'"); // Italian
// returns: perche-l-erba-e-verde
```

The extension [iconv][1] must be installed in PHP.

### Credits

Matteo Spinelli (http://cubiq.org) [php-clean-url-generator][2]

[1]: http://php.net/manual/en/book.iconv.php
[2]: http://cubiq.org/the-perfect-php-clean-url-generator
