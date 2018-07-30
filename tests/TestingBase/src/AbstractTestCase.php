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

namespace PhpCsFixer\TestingBase;

use PHPUnit\Framework\TestCase as BaseTestCase;
use PHPUnitGoodPractices\Traits\ExpectationViaCodeOverAnnotationTrait;
use PHPUnitGoodPractices\Traits\ExpectOverSetExceptionTrait;
use PHPUnitGoodPractices\Traits\IdentityOverEqualityTrait;
use PHPUnitGoodPractices\Traits\ProphecyOverMockObjectTrait;
use PHPUnitGoodPractices\Traits\ProphesizeOnlyInterfaceTrait;

if (trait_exists(ProphesizeOnlyInterfaceTrait::class)) {
    /**
     * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
     */
    abstract class AbstractTestCase extends BaseTestCase
    {
        use ExpectationViaCodeOverAnnotationTrait;
        use ExpectOverSetExceptionTrait;
        use IdentityOverEqualityTrait;
        use ProphecyOverMockObjectTrait;
        use ProphesizeOnlyInterfaceTrait;
    }
} else {
    /**
     * Version without traits for cases when one requested `PhpCsFixer\Test\TestCase` used in deprecated `PhpCsFixer\Test\*`
     * and not `PhpCsFixer\TestingBase\AbstractTestCase` directly.
     *
     * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
     *
     * @todo 3.0 To be removed when we clean up composer prod-autoloader from dev-packages.
     */
    abstract class AbstractTestCase extends BaseTestCase
    {
    }
}
