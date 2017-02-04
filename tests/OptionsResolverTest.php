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

use PhpCsFixer\OptionsResolver;

/**
 * @internal
 */
final class OptionsResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testSetDefault()
    {
        $optionsResolver = new OptionsResolver();

        $this->assertSame(
            $optionsResolver,
            $optionsResolver->setDefault('foo', 'bar')
        );

        $this->assertSame(
            array('foo' => 'bar'),
            $optionsResolver->resolve(array())
        );
    }

    public function testGetDefault()
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefault('foo', 'bar');

        $this->assertSame('bar', $optionsResolver->getDefault('foo'));
    }

    public function testGetDefaultWithUndefinedOption()
    {
        $optionsResolver = new OptionsResolver();

        $this->setExpectedException('Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException');

        $optionsResolver->getDefault('foo');
    }

    public function testGetDefaultWithNoDefaultValue()
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefined('foo');

        $this->setExpectedException('Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException');

        $optionsResolver->getDefault('foo');
    }

    public function testGetDefaults()
    {
        $optionsResolver = new OptionsResolver();

        $this->assertSame(
            array(),
            $optionsResolver->getDefaults()
        );

        $optionsResolver->setDefault('foo', 'bar');

        $this->assertSame(
            array('foo' => 'bar'),
            $optionsResolver->getDefaults()
        );
    }

    public function testRemove()
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefault('foo', 'bar');

        $this->assertSame(
            $optionsResolver,
            $optionsResolver->remove('foo')
        );

        $this->setExpectedException('Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException');

        $optionsResolver->resolve(array('foo' => 'bar'));
    }

    public function testGetDefaultAfterRemove()
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefault('foo', 'bar');
        $optionsResolver->remove('foo');

        $this->setExpectedException('Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException');

        $optionsResolver->getDefault('foo');
    }

    public function testGetDefaultAfterRemoveAndDefine()
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefault('foo', 'bar');
        $optionsResolver->remove('foo');
        $optionsResolver->setDefined('foo');

        $this->setExpectedException('Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException');

        $optionsResolver->getDefault('foo');
    }

    public function testGetDefaultsAfterRemove()
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefault('foo', 'bar');
        $optionsResolver->remove('foo');

        $this->assertSame(array(), $optionsResolver->getDefaults());
    }

    public function testClear()
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefault('foo', 'bar');

        $this->assertSame(
            $optionsResolver,
            $optionsResolver->clear()
        );

        $this->setExpectedException('Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException');

        $optionsResolver->resolve(array('foo' => 'bar'));
    }

    public function testGetDefaultAfterClear()
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefault('foo', 'bar');
        $optionsResolver->clear();

        $this->setExpectedException('Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException');

        $optionsResolver->getDefault('foo');
    }

    public function testGetDefaultAfterClearAndDefine()
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefault('foo', 'bar');
        $optionsResolver->clear();
        $optionsResolver->setDefined('foo');

        $this->setExpectedException('Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException');

        $optionsResolver->getDefault('foo');
    }

    public function testGetDefaultsAfterClear()
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefault('foo', 'bar');
        $optionsResolver->setDefault('bar', 'baz');
        $optionsResolver->clear();

        $this->assertSame(array(), $optionsResolver->getDefaults());
    }
}
