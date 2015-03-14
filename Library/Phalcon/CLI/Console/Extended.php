<?php
namespace Phalcon\CLI\Console;

/**
 * Phalcon\CLI\Console\Extended
 *
 * @version 0.1
 * @author  Sebastian Arrubia <sarrubia@gmail.com>
 *
 */
class Extended extends \Phalcon\CLI\Console
{

    private $tasksDir = '';
    private $documentation = array();


    public function handle(array $arguments)
    {
        if (isset($arguments['task']) && in_array($arguments['task'], array('-h','--help','help'))) {
            $this->setTasksDir();
            $this->createHelp();
            $this->showHelp();
            return;
        } elseif (isset($arguments['action']) && in_array($arguments['action'], array('-h','--help','help'))) {
            $this->setTasksDir();
            $this->createHelp();
            $this->showTaskHelp($arguments['task']);
            return;
        }

        parent::handle($arguments);
    }

    private function setTasksDir()
    {
        $config = $this->getDI()->get('config');

        if (!is_dir($config['tasksDir'])) {
            throw new \Phalcon\CLI\Console\Exception("Invalid provided tasks Dir");
        }

        $this->tasksDir = $config['tasksDir'];
    }

    private function createHelp()
    {
        $scannedTasksDir = array_diff(scandir($this->tasksDir), array('..', '.'));

        $config = $this->getDI()->get('config');

        if (isset($config['annotationsAdapter'])) {
            if ($config['annotationsAdapter'] == 'memory') {
                $reader = new \Phalcon\Annotations\Adapter\Memory();
            } elseif ($config['annotationsAdapter'] == 'apc') {
                $reader = new \Phalcon\Annotations\Adapter\Apc();
            } else {
                $reader = new \Phalcon\Annotations\Adapter\Memory();
            }
        } else {
            $reader = new \Phalcon\Annotations\Adapter\Memory();
        }

        foreach ($scannedTasksDir as $taskFile) {
            $taskClass = str_replace('.php', '', $taskFile);
            $taskName  = strtolower(str_replace('Task', '', $taskClass));

            $this->documentation[$taskName] = array('description'=>array(''), 'actions'=>array());

            $reflector = $reader->get($taskClass);
            $annotations = $reflector->getClassAnnotations();

            $methodAnnotations = $reflector->getMethodsAnnotations();

            if ($annotations) {
                //Class Annotations
                foreach ($annotations as $annotation) {
                    if ($annotation->getName() == 'description') {
                        $this->documentation[$taskName]['description'] = $annotation->getArguments();
                    }
                }
                //Method Annotations
                if ($methodAnnotations) {
                    foreach ($methodAnnotations as $action => $collection) {
                        $actionName = strtolower(str_replace('Action', '', $action));

                        $this->documentation[$taskName]['actions'][$actionName]=array();

                        $actionAnnotations = $collection->getAnnotations();

                        foreach ($actionAnnotations as $actAnnotation) {
                            $_anotation = $actAnnotation->getName();
                            if ($_anotation == 'description') {
                                $this->documentation[$taskName]['actions'][$actionName]['description'] = $actAnnotation->getArguments();
                            } elseif ($_anotation == 'param') {
                                $this->documentation[$taskName]['actions'][$actionName]['params'][] = $actAnnotation->getArguments();
                            }
                        }
                    }
                }
            }
        }
    }

    private function showHelp()
    {
        $config = $this->getDI()->get('config');
        $helpOutput = PHP_EOL;
        if (isset($config['appName'])) {
            $helpOutput .= $config['appName'] . ' ';
        }

        if (isset($config['version'])) {
            $helpOutput .= $config['version'];
        }

        echo $helpOutput . PHP_EOL;
        echo PHP_EOL . 'Usage:' . PHP_EOL;
        echo PHP_EOL;
        echo '           command [<task> [<action> [<param1> <param2> ... <paramN>] ] ]'. PHP_EOL;
        echo PHP_EOL;
        echo PHP_EOL . 'To show task help type:' . PHP_EOL;
        echo PHP_EOL;
        echo '           command <task> -h | --help | help'. PHP_EOL;
        echo PHP_EOL;
        echo 'Available tasks '.PHP_EOL;
        foreach ($this->documentation as $task => $doc) {
            echo  PHP_EOL;
            echo '    '. $task . PHP_EOL ;

            foreach ($doc['description'] as $line) {
                echo '            '.$line . PHP_EOL;
            }
        }
    }

    private function showTaskHelp($taskTogetHelp)
    {
        $config = $this->getDI()->get('config');
        $helpOutput = PHP_EOL;
        if (isset($config['appName'])) {
            $helpOutput .= $config['appName'] . ' ';
        }

        if (isset($config['version'])) {
            $helpOutput .= $config['version'];
        }

        echo $helpOutput . PHP_EOL;
        echo PHP_EOL . 'Usage:' . PHP_EOL;
        echo PHP_EOL;
        echo '           command [<task> [<action> [<param1> <param2> ... <paramN>] ] ]'. PHP_EOL;
        echo PHP_EOL;
        foreach ($this->documentation as $task => $doc) {
            if ($taskTogetHelp == $task) {
                echo  PHP_EOL;
                echo "Task: " . $task . PHP_EOL . PHP_EOL ;

                foreach ($doc['description'] as $line) {
                    echo '  '.$line . PHP_EOL;
                }
                echo  PHP_EOL;
                echo 'Available actions:'.PHP_EOL.PHP_EOL;

                foreach ($doc['actions'] as $actionName => $aDoc) {
                    echo '           '.$actionName . PHP_EOL;
                    if (isset($aDoc['description'])) {
                        echo '               '.implode(PHP_EOL, $aDoc['description']) . PHP_EOL;
                    }
                    echo  PHP_EOL;
                    if (isset($aDoc['params']) && is_array($aDoc['params'])) {
                        echo '               Parameters:'.PHP_EOL;
                        foreach ($aDoc['params'] as $param) {
                            if (is_array($param)) {
                                $_to_print = '';
                                if (isset($param[0]['name'])) {
                                    $_to_print = $param[0]['name'];
                                }

                                if (isset($param[0]['type'])) {
                                    $_to_print .= ' ( '.$param[0]['type'].' )';
                                }

                                if (isset($param[0]['description'])) {
                                    $_to_print .= ' '.$param[0]['description'].PHP_EOL;
                                }

                                if (!empty($_to_print)) {
                                    echo '                   '.$_to_print;
                                }
                            }
                        }
                    }
                }
                break;
            }
        }
    }
}
