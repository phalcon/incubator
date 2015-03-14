#Phalcon\CLI\Console\Extended
Extended "Phalcon\CLI\Console\Extended" class that uses **annotations** in order to create automatically a help description.

##How to use it?
The parameters **-h**, **--help** or **help** could be used in order to get the help documentation automatically. For instance:

**To get the tasks list and their description**

> ./yourAppCommand -h

or

> ./yourAppCommand --help

or

> ./yourAppCommand help

**To get the task's actions list and their description, even the parameters description**

> ./yourAppCommand <task> -h

or

> ./yourAppCommand <task> --help

or

> ./yourAppCommand <task> help

##How to set it up?

Firstly you need to add some important values into the config.
```php
$config =  new \Phalcon\Config(array(

    'appName' => 'My Console App',
    'version' => '1.0',

    /**
    * tasksDir is the absolute path to your tasks directory
    * For instance, 'tasksDir' => realpath(dirname(dirname(__FILE__))).'/tasks',
    */
    'tasksDir' => '/path/to/your/project/tasks',
    
    /**
    * annotationsAdapter is the choosen adapter to read annotations. 
    * Available adapters: memory | apc
    * Adapter by default: memory
    */
    'annotationsAdapter' => 'memory',
    
    'printNewLine' => true
));
```

Second you must to create an instance of the DI class and add the created config class under key 'config'.

```php
$di = new \Phalcon\DI\FactoryDefault\CLI();
$di->set("config",function () use ($config) {
    return $config;
});
```

Well, it's time to create an instance of Extended Console Class to handle the calls
```php
$console = new \Phalcon\CLI\Console\Extended();
//Seting the above DI
$console->setDI($di);

/**
 * Process the console arguments
 */
$arguments = array();
$params = array();

foreach ($argv as $k => $arg) {
    if ($k == 1) {
        $arguments['task'] = $arg;
    } elseif ($k == 2) {
        $arguments['action'] = $arg;
    } elseif ($k >= 3) {
        $arguments['params'][] = $arg;
    }
}

try {
    $console->handle($arguments);
} catch (Exception $e) {
    echo $e->getMessage();
    exit(255);
}
```
##How to add task description?
The annotation tags that have been used to add tasks description are described below:

| Annotation| Description|
| :------------- |:-------------|
| description      | Is the Task or Action description. Could be one line or multiples lines|
| param      | Is the parameter documentation of the Action. The "param " annotation have 3 variables Name, Type and description|

###Task Example
Assume that we have developed a task, to list a directory's content. So the file of the task must be located within the tasks folder. **For instance: /path/to/your/project/tasks/LsTask.php**

Pay attention to the file name. This must be named as **<TaskName>Task.php**

```php
/**
 * Class LsTask
 * @description("List directory content","The content will be displayed in the standard output")
 */
class LsTask extends \Phalcon\CLI\Task
{
    /**
     * @description("Non recursive list")
     */
    public function mainAction()
    {
        echo 'Content list:'.PHP_EOL;
        //Code to iterate a directory and show the content
    }

    /**
     * @description("Human readable action")
     * @param({'type'='string', 'name'='directory', 'description'='directory to be listed' })
     * @param({'type'='string', 'name'='Size unit', 'description'='Unit size to be shown' })
     */
    public function hrAction(array $params) {
        $directoryToList = $params[0];
        $unitSize = $params[1];
        //Code to iterate a directory and show the content
    }
}
```
So the above example should looks like:
> ./yourAppCommand ls --help

```
My Console App 1.0
Usage:
           command [<task> [<action> [<param1> <param2> ... <paramN>] ] ]
Task: ls
  List directory content
  The content will be displayed in the standard output

Available actions:
           main
               Non recursive list
           hr
               Human readable action
               Parameters:
                   directory ( string ) directory to be listed
                   Size unit ( string ) Unit size to be shown

```
