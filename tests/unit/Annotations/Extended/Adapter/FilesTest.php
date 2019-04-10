<?php

namespace Phalcon\Test\Annotations\Extended\Adapter;

use Phalcon\Test\Codeception\UnitTestCase as Test;
use Phalcon\Annotations\Reflection;
use Phalcon\Annotations\Extended\Adapter\Files;

class FilesTest extends Test
{
    /** @test */
    public function shouldReadFromFileDirectoryWithoutAnyAdditionalParameter()
    {
        $reflection = $this->getReflection();

        $annotations = new Files();

        $this->tester->amInPath(
            sys_get_temp_dir()
        );

        $this->tester->writeToFile(
            'read-1.php',
            '<?php return ' . var_export($reflection, true) . '; '
        );

        $this->assertEquals(
            $reflection,
            $annotations->read('read-1')
        );

        $this->tester->deleteFile('read-1.php');
    }

    /** @test */
    public function shouldReadFromFileDirectoryWithAnnotationsDir()
    {
        $reflection = $this->getReflection();

        $annotations = new Files(
            [
                'annotationsDir' => codecept_output_dir(),
            ]
        );

        $this->tester->amInPath(
            codecept_output_dir()
        );

        $this->tester->writeToFile(
            'read-2.php',
            '<?php return ' . var_export($reflection, true) . '; '
        );

        $this->assertEquals(
            $reflection,
            $annotations->read('read-2')
        );

        $this->tester->deleteFile('read-2.php');
    }

    /** @test */
    public function shouldWriteToTheFileDirectoryWithoutAnyAdditionalParameter()
    {
        $reflection = $this->getReflection();

        $annotations = new Files();

        $this->assertTrue(
            $annotations->write(
                'write-1',
                $reflection
            )
        );

        $this->tester->amInPath(
            sys_get_temp_dir()
        );

        $this->tester->seeFileFound('write-1.php');

        $this->tester->seeFileContentsEqual(
            '<?php return ' . var_export($reflection, true) . '; '
        );

        $this->tester->deleteFile('write-1.php');
    }

    /** @test */
    public function shouldWriteToTheFileDirectoryWithAnnotationsDir()
    {
        $reflection = $this->getReflection();

        $annotations = new Files(
            [
                'annotationsDir' => codecept_output_dir(),
            ]
        );

        $this->assertTrue(
            $annotations->write('write-2', $reflection)
        );

        $this->tester->amInPath(
            codecept_output_dir()
        );

        $this->tester->seeFileFound('write-2.php');

        $this->tester->seeFileContentsEqual(
            '<?php return ' . var_export($reflection, true) . '; '
        );

        $this->tester->deleteFile('write-2.php');
    }

    /** @test */
    public function shouldFlushTheFileDirectoryStorageWithoutAnyAdditionalParameter()
    {
        $reflection = $this->getReflection();

        $annotations = new Files();

        $this->tester->amInPath(
            sys_get_temp_dir()
        );

        $this->tester->writeToFile(
            'flush-1.php',
            '<?php return ' . var_export($reflection, true) . '; '
        );

        $this->tester->writeToFile(
            'flush-2.php',
            '<?php return ' . var_export($reflection, true) . '; '
        );

        $this->assertTrue(
            $annotations->flush()
        );

        $this->tester->dontSeeFileFound('flush-1.php');
        $this->tester->dontSeeFileFound('flush-2.php');
    }

    /** @test */
    public function shouldFlushTheFileDirectoryStorageWithAnnotationsDir()
    {
        $reflection = $this->getReflection();

        $annotations = new Files(
            [
                'annotationsDir' => codecept_output_dir(),
            ]
        );

        $this->tester->amInPath(
            codecept_output_dir()
        );

        $this->tester->writeToFile(
            'flush-3.php',
            '<?php return ' . var_export($reflection, true) . '; '
        );

        $this->tester->writeToFile(
            'flush-4.php',
            '<?php return ' . var_export($reflection, true) . '; '
        );

        $this->assertTrue(
            $annotations->flush()
        );

        $this->tester->dontSeeFileFound('flush-3.php');
        $this->tester->dontSeeFileFound('flush-4.php');
    }

    /** @test */
    public function shouldReadAndWriteFromFileDirectoryWithoutAnyAdditionalParameter()
    {
        $reflection = $this->getReflection();

        $annotations = new Files();

        $this->assertTrue(
            $annotations->write(
                'read-write-1',
                $reflection
            )
        );

        $this->assertEquals(
            $reflection,
            $annotations->read('read-write-1')
        );

        $this->tester->amInPath(
            sys_get_temp_dir()
        );

        $this->tester->seeFileContentsEqual(
            '<?php return ' . var_export($reflection, true) . '; '
        );

        $this->tester->deleteFile('read-write-1.php');
    }

    /** @test */
    public function shouldReadAndWriteFromFileDirectoryWithAnnotationsDir()
    {
        $reflection = $this->getReflection();

        $annotations = new Files(
            [
                'annotationsDir' => codecept_output_dir(),
            ]
        );

        $this->assertTrue(
            $annotations->write('read-write-2', $reflection)
        );

        $this->assertEquals(
            $reflection,
            $annotations->read('read-write-2')
        );

        $this->tester->amInPath(
            codecept_output_dir()
        );

        $this->tester->seeFileContentsEqual(
            '<?php return ' . var_export($reflection, true) . '; '
        );

        $this->tester->deleteFile('read-write-2.php');
    }


    protected function getReflection()
    {
        return Reflection::__set_state(
            [
                '_reflectionData' => [
                    'class'      => [],
                    'methods'    => [],
                    'properties' => [],
                ],
            ]
        );
    }
}
