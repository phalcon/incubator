# Phalcon\Http\Client

Http Request and Response

## Request

Request class to make request to URI

### Examples

```php
use Phalcon\Http\Client\Request;

// get available provider Curl or Stream
$provider = Request::getProvider();

$provider->setBaseUri('http://example.com/api/');

$provider->header->set('Accept', 'application/json');

// GET request to http://example.com/api/me/images?access_token=1234 and return response
$response = $provider->get('me/images', [
    'access_token' => 1234
]);

echo $response->body;

// POST multipart/form-data request to http://example.com/api/me/images
$response = $provider->post('me/images', [
    'access_token' => 1234,
    'image' => '@/home/mine/myimage.jpg'
]);

echo $response->body;
echo $response->header->get('Content-Type');
echo $response->header->statusCode;

// DELETE request to http://example.com/api/me/images
$response = $provider->delete('me/images', [
    'access_token' => 1234,
    'image_id' => '321'
]);

echo $response->body;
```
