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

namespace PhpCsFixer\Tests\Test;

use PHPUnit\Framework\TestCase as BaseTestCase;
use PHPUnitGoodPractices\ExpectationViaCodeOverAnnotationTrait;
use PHPUnitGoodPractices\ExpectOverSetExceptionTrait;
use PHPUnitGoodPractices\IdentityOverEqualityTrait;
use PHPUnitGoodPractices\ProphecyOverMockObjectTrait;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
abstract class TestCase extends BaseTestCase
{
    use ExpectationViaCodeOverAnnotationTrait;
//    use ExpectOverSetExceptionTrait; // todo - update usage
   use IdentityOverEqualityTrait; // todo - fix violation detection in GoodPractices
    use ProphecyOverMockObjectTrait;
}
