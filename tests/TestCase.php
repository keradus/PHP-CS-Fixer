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
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
abstract class TestCase extends BaseTestCase
{
    protected function tearDown()
    {
        parent::tearDown();

        $this->cleanPropertiesPerInstance();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        self::cleanPropertiesPerClass();

        var_dump(sprintf(
            "\nMemory: %d MB, peak: %d MB",
            floor(memory_get_usage(true) / 1024 / 1024),
            floor(memory_get_peak_usage(true) / 1024 / 1024)
        ));
    }

    /**
     * @var ?array
     */
    private static $propertiesToClean;

    protected function cleanPropertiesPerInstance() {
        $this->collectPropertiesToClean();

        self::cleanPropertiesPerContext($this, self::$propertiesToClean['perInstance']);
    }

    protected static function cleanPropertiesPerClass() {
        if (null === self::$propertiesToClean) {
            throw new \LogicException('Run `collectPropertiesToClean` before calling `cleanPropertiesPerClass`.');
        }

        self::cleanPropertiesPerContext(get_called_class(), self::$propertiesToClean['perClass']);

        self::$propertiesToClean = null;
    }

    private function collectPropertiesToClean() {
        if (null !== self::$propertiesToClean) {
            return;
        }

        $refl = new \ReflectionObject($this);

        $perInstance = [];
        $perClass = [];

        foreach ($refl->getProperties() as $property) {
            if (0 === strpos($property->getDeclaringClass()->getName(), 'PHPUnit')) {
                continue;
            }

            if ($property->isStatic()) {
                $perClass[] = $property->getName();
            }
            else {
                $perInstance[] = $property->getName();
            }
        }

        self::$propertiesToClean = [
            'perClass' => $perClass,
            'perInstance' => $perInstance,
        ];
    }

    private static function cleanPropertiesPerContext($context, array $properties)
    {
        foreach ($properties as $var) {
            $prop = new \ReflectionProperty($context, $var);
            $prop->setAccessible(true);
            $prop->setValue($context, null);
        }
    }
}
