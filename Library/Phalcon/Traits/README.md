# Phalcon\Traits

Here is a collection of Traits that are often used or can be useful in everyday Phalcongelist life.

## Phalcon\Traits\ConfigurableTrait

Allows to define parameters which can be set by passing them to the class constructor.
These parameters should be defined in the `$configurable` array. 

```php
use Phalcon\Traits\ConfigurableTrait;

class MyAdapter
{
    use ConfigurableTrait;
    
    protected $host;
    protected $someParameter;
    protected $protectedParameter;
    
    protected $configurable = [
        'host',
        'snake_cased'
    ];
    
    public function __construct(array $options)
    {
        $this->setConfig($config);
    }
    
    protected function setHost($host)
    {
        $this->host = $host;
    }
    
    protected function setSnakeCased($someParameter)
    {
        $this->someParameter = $someParameter;
    }
}
```
