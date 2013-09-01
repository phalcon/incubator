
# Phalcon\Http

Uri utility

## Uri

The utility to parse URI strings. Resolve absolute, relative URN and querystrings. Build new URI from actual object's statement.

### Examples

```php
use Phalcon\Client\Http\Uri;

$uri1 = new Uri('http://phalconphp.com/foo/bar/baz?var1=a&var2=1');

$uri2 = $uri1->resolve('/last');
echo $uri2->build(); // http://phalconphp.com/last?var1=a&var2=1


$uri3 = $uri1->resolve('last');
echo $uri3->build(); // http://phalconphp.com/foo/bar/baz/last?var1=a&var2=1

$uri4 = new Uri(array(
    'scheme' => 'https',
    'host' => 'admin.example.com',
    'user' => 'john',
    'pass' => 'doe'
));

$uri5 = $uri1->resolve($uri4);
echo $uri5->build(); // https://john:doe@admin.example.com/foo/bar/baz?var1=a&var2=1
```
