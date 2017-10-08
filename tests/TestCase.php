<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * @internal
 */
final class PHPUnitTearDownPropertiesManager
{
    private $classProperties = [];
    private $instanceProperties = [];
    private $instance;

    public function __construct($instance)
    {
        $this->instance = $instance;

        $reflection = new \ReflectionObject($this->instance);

        foreach ($reflection->getProperties() as $property) {
            if (1 === preg_match('/^PHPUnit[\\\_]/', $property->getDeclaringClass()->getName())) {
                continue;
            }

            if ($property->isStatic()) {
                $this->classProperties[] = $property->getName();
            }
            else {
                $this->instanceProperties[] = $property->getName();
            }
        }
    }

    public function cleanInstance() {
        $this->cleanPropertiesPerContext($this->instance, $this->instanceProperties);
    }

    public function cleanClass() {
        $this->cleanPropertiesPerContext(get_class($this->instance), $this->classProperties);
    }

    private static function cleanPropertiesPerContext($context, array $properties)
    {
        foreach ($properties as $var) {
            $property = new \ReflectionProperty($context, $var);
            $property->setAccessible(true);
            $property->setValue($context, null);
        }
    }
}

trait PHPUnitTearDownPropertiesTrait
{
    /**
     * @var ?PHPUnitTearDownPropertiesManager
     * @internal
     */
    private static $propertiesToClean;

    private function cleanPropertiesPerInstance() {
        if (null === self::$propertiesToClean) {
            self::$propertiesToClean = new PHPUnitTearDownPropertiesManager($this);
        }

        self::$propertiesToClean->cleanInstance();
    }

    private static function cleanPropertiesPerClass() {
        if (null === self::$propertiesToClean) {
            throw new \LogicException('It is needed to run `cleanPropertiesPerInstance` before calling `cleanPropertiesPerClass`.');
        }

        self::$propertiesToClean->cleanClass();

        self::$propertiesToClean = null;
    }
}

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
abstract class TestCase extends BaseTestCase
{
    use PHPUnitTearDownPropertiesTrait;

    protected function tearDown()
    {
        $this->cleanPropertiesPerInstance();

        parent::tearDown();
    }

    public static function tearDownAfterClass()
    {
        self::cleanPropertiesPerClass();
//
//        var_dump(sprintf(
//            "\nMemory: %d MB, peak: %d MB",
//            floor(memory_get_usage(true) / 1024 / 1024),
//            floor(memory_get_peak_usage(true) / 1024 / 1024)
//        ));

        parent::tearDownAfterClass();
    }
}
