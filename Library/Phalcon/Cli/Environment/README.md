# Phalcon\Cli\Environment

This component provides functionality that helps writing CLI oriented code that has runtime-specific execution params.

## How to use it?

`Environment` provides some useful methods:

* `Environment::isWindows` - Checks if currently running under MS Windows
* `Environment::isAnsicon` - When currently running under MS Windows checks if [ANSI x3.64][1] is supported and enabled
* `Environment::isConsole` - Checks if running in a console environment (CLI)
* `Environment::isInteractive` - Checks if the file descriptor is an interactive terminal
* `Environment::hasColorSupport` - Checks the supports of colorization
* `Environment::getDimensions` - Gets the terminal dimensions based on the current environment
* `Environment::setDimensions` - Sets terminal dimensions
* `Environment::getModeCon` - Runs and parses Microsoft DOS `MODE CON` command if it's available (suppressing any error output)
* `Environment::getSttySize` - Runs and parses `stty size` command if it's available (suppressing any error output)
* `Environment::getNumberOfColumns` - Gets the number of columns of the terminal
* `Environment::getNumberOfRows` - Gets the number of rows of the terminal

## Integration

Firstly you need to create an environment aware Console Application.

```php
namespace MyAwesomeApplication;

use Phalcon\Cli\Environment\EnvironmentAwareInterface;
use Phalcon\Di\FactoryDefault\Cli as CliDi;
use Phalcon\Cli\Console as PhConsole;
use Phalcon\DiInterface;

class Console extends PhConsole implements EnvironmentAwareInterface
{
    protected $environment;

    public function __construct(DiInterface $di = null)
    {
        $di = $di ?: new CliDi;
        
        parent::__construct($di);
    }
    
    public function setEnvironment(Environment $environment)
    {
        $this->environment = $environment;
        
        return $this;
    }
    
    public function getEnvironment()
    {
        return $this->environment;
    }
}
```

Then you need to add `Environment` component to your Console Application.

```php
use Phalcon\Cli\Environment\Environment;
use MyAwesomeApplication\Console;

$application = new Console;

$application->setEnvironment(new Environment);
```

[1]: http://vt100.net/annarbor/aaa-ug/section13.html
