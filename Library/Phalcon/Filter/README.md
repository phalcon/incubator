Phalcon\Filter
==============

Extension of [built-in Phalcon filters](http://docs.phalconphp.com/en/latest/reference/filter.html#types-of-built-in-filters).

Usage example of filters available here:

File\Mime2Extension
-------------------

Converts file mime type to extension. List of mime types taken from [here](https://raw.githubusercontent.com/EllisLab/CodeIgniter/develop/application/config/mimes.php)

```php
// define in services.php
$di->set('filter', function () {
    $filter = new \Phalcon\Filter();
    $filter->add('mime2extension', new \Phalcon\Filter\File\Mime2Extension());
    return $filter;
}, true);

// example usage in controller
foreach ($this->request->getUploadedFiles() as $file) {
    // outputs jpg, png, gif etc.
    echo $this->filter->sanitize($file->getRealType(), 'mim2extension');
}
```