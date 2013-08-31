
# Phalcon\Client\Http

Http Request and Response and Uri utility

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

$uri5 = $uri1->resolce($uri4);
echo $uri5->build(); // https://john:doe@admin.example.com/foo/bar/baz?var1=a&var2=1
```

## Request

Request class to make request to URI

### Examples

```php
use Phalcon\Client\Http\Request;

$provider  = Request::getProvider(); // get available provider Curl or Stream

$provider->setBaseUri('http://example.com/api');

$provider->header->set('Accept', 'application/json');

$response = $provider->get('me/images', array(
    'access_token' => 1234
)); // GET request to http://example.com/api/me/images?access_token=1234 and return response

echo $response->body;

$response = $provider->post('me/images', array(
    'access_token' => 1234,
    'image' => '@/home/mine/myimage.jpg'
)); // POST multipart/form-data request to http://example.com/api/me/images 

echo $response->body;
echo $response->header->get('Content-Type');
echo $response->header->statusCode;

$response = $provider->delete('me/images', array(
    'access_token' => 1234,
    'image_id' => '321'
)); // DELETE request to http://example.com/api/me/images 

echo $response->body;

```
