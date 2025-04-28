<?php

declare(strict_types=1);

namespace Tests\Feature\AppFreeCommands;

/*
 * All classes extending AppFreeDto should only have readonly class properties
 * which are set via the constructor. There can be no other class properties.
 * This test finds all classes extending from AppFreeDto which are
 * located in the directory AppFreeCommands and checks this via reflection
 */


use AppFree\AppFreeCommands\AppFreeDto;
use ReflectionClass;
use Tests\TestCase;

class AppFreeDtoTest extends TestCase
{
    public const string APP_APP_FREE_COMMANDS_DIR = __DIR__ . "/../../../app/AppFreeCommands";

    private function loadDtoClasses(string $dir): void
    {
        $files = scandir($dir);
        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $file;

            if (is_dir($path)) {
                $this->loadDtoClasses($path);
            } elseif (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                //fixme this only works correctly for files only containing class definitions
                require_once $path;
            }
        }
    }

    private function getDescendantsOfAppFreeDto()
    {
        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, AppFreeDto::class)) {
                yield $class;
            }
        }
    }

    //    function isSerializable( $var )
    //    {
    //        try {
    //            serialize( $var );
    //            return TRUE;
    //        } catch( Exception $e ) {
    //            return FALSE;
    //        }
    //    }

    public function testCheckAllDtoClassesHaveOnlyReadonlyAttribute()
    {
        $this->loadDtoClasses(self::APP_APP_FREE_COMMANDS_DIR);
        foreach ($this->getDescendantsOfAppFreeDto() as $afd) {

            // check that all DTO class properties are readonly
            $reflection = new ReflectionClass($afd);
            $properties = $reflection->getProperties();
            foreach ($properties as $property) {
                $this->assertTrue($property->isReadOnly(), 'Class ' . $reflection->getFileName() . "::" . $property->getName() . " is not readonly");
            }
        }
    }
}
