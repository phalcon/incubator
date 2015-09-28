# Phalcon\Http\Client

Http Request and Response

## Request

Request class to make request to URI

### Examples

```php
use Phalcon\Http\Client\Request;

$provider  = Request::getProvider(); // get available provider Curl or Stream

$provider->setBaseUri('http://example.com/api/');

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
