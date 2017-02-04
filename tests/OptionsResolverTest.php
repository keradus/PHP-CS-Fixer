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

    public function testSetDescriptionWithUndefinedOption()
    {
        $optionsResolver = new OptionsResolver();

        $this->setExpectedException('Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException');

        $optionsResolver->setDescription('foo', 'Foo');
    }

    public function testSetDescription()
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefined('foo');

        $this->assertSame(
            $optionsResolver,
            $optionsResolver->setDescription('foo', 'Foo')
        );
    }

    public function testGetDescription()
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefined('foo');

        $this->assertNull($optionsResolver->getDescription('foo'));

        $optionsResolver->setDescription('foo', 'Foo');

        $this->assertSame('Foo', $optionsResolver->getDescription('foo'));
    }

    public function testGetDescriptionWithUndefinedOption()
    {
        $optionsResolver = new OptionsResolver();

        $this->setExpectedException('Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException');

        $optionsResolver->getDescription('foo');
    }

    public function testSetAllowedValuesWithUndefinedOption()
    {
        $optionsResolver = new OptionsResolver();

        $this->setExpectedException('Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException');

        $optionsResolver->setAllowedValues('foo', array('bar', 'baz'));
    }

    public function testSetAllowedValues()
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefined('foo');

        $this->assertSame(
            $optionsResolver,
            $optionsResolver->setAllowedValues('foo', 'bar')
        );

        $this->assertSame(
            $optionsResolver,
            $optionsResolver->setAllowedValues('foo', array('bar', 'baz'))
        );
    }

    public function testGetAllowedValues()
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefined('foo');

        $this->assertNull($optionsResolver->getAllowedValues('foo'));

        $optionsResolver->setAllowedValues('foo', 'bar');

        $this->assertSame(
            array('bar'),
            $optionsResolver->getAllowedValues('foo')
        );

        $optionsResolver->setAllowedValues('foo', array('bar', 'baz'));

        $this->assertSame(
            array('bar', 'baz'),
            $optionsResolver->getAllowedValues('foo')
        );
    }

    public function testGetAllowedValuesWithUndefinedOption()
    {
        $optionsResolver = new OptionsResolver();

        $this->setExpectedException('Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException');

        $optionsResolver->getAllowedValues('foo');
    }

    public function testAddAllowedValues()
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefined('foo');

        $this->assertSame(
            $optionsResolver,
            $optionsResolver->addAllowedValues('foo', 'bar')
        );

        $this->assertSame(
            array('bar'),
            $optionsResolver->getAllowedValues('foo')
        );

        $optionsResolver->addAllowedValues('foo', 'baz');

        $this->assertSame(
            array('bar', 'baz'),
            $optionsResolver->getAllowedValues('foo')
        );
    }

    public function testAddAllowedValuesWithUndefinedOption()
    {
        $optionsResolver = new OptionsResolver();

        $this->setExpectedException('Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException');

        $optionsResolver->addAllowedValues('foo', 'bar');
    }

    public function testSetAllowedTypesWithUndefinedOption()
    {
        $optionsResolver = new OptionsResolver();

        $this->setExpectedException('Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException');

        $optionsResolver->setAllowedTypes('foo', array('bar', 'baz'));
    }

    public function testSetAllowedTypes()
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefined('foo');

        $this->assertSame(
            $optionsResolver,
            $optionsResolver->setAllowedTypes('foo', 'bar')
        );

        $this->assertSame(
            $optionsResolver,
            $optionsResolver->setAllowedTypes('foo', array('bar', 'baz'))
        );
    }

    public function testGetAllowedTypes()
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefined('foo');

        $this->assertNull($optionsResolver->getAllowedTypes('foo'));

        $optionsResolver->setAllowedTypes('foo', 'bar');

        $this->assertSame(
            array('bar'),
            $optionsResolver->getAllowedTypes('foo')
        );

        $optionsResolver->setAllowedTypes('foo', array('bar', 'baz'));

        $this->assertSame(
            array('bar', 'baz'),
            $optionsResolver->getAllowedTypes('foo')
        );
    }

    public function testGetAllowedTypesWithUndefinedOption()
    {
        $optionsResolver = new OptionsResolver();

        $this->setExpectedException('Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException');

        $optionsResolver->getAllowedTypes('foo');
    }

    public function testAddAllowedTypesWithUndefinedOption()
    {
        $optionsResolver = new OptionsResolver();

        $this->setExpectedException('Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException');

        $optionsResolver->addAllowedTypes('foo', 'bar');
    }

    public function testAddAllowedTypes()
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefined('foo');

        $this->assertSame(
            $optionsResolver,
            $optionsResolver->addAllowedTypes('foo', 'bar')
        );

        $this->assertSame(
            array('bar'),
            $optionsResolver->getAllowedTypes('foo')
        );

        $optionsResolver->addAllowedTypes('foo', 'baz');

        $this->assertSame(
            array('bar', 'baz'),
            $optionsResolver->getAllowedTypes('foo')
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

    public function testGetDescriptionAfterRemove()
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefined('foo');
        $optionsResolver->setDescription('foo', 'Foo');
        $optionsResolver->remove('foo');

        $this->setExpectedException('Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException');

        $optionsResolver->getDescription('foo');
    }

    public function testGetDescriptionAfterRemoveAndDefine()
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefined('foo');
        $optionsResolver->setDescription('foo', 'Foo');
        $optionsResolver->remove('foo');
        $optionsResolver->setDefined('foo');

        $this->assertNull($optionsResolver->getDescription('foo'));
    }

    public function testGetAllowedValuesAfterRemove()
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefined('foo');
        $optionsResolver->setAllowedValues('foo', 'bar');
        $optionsResolver->remove('foo');

        $this->setExpectedException('Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException');

        $optionsResolver->getAllowedValues('foo');
    }

    public function testGetAllowedValuesAfterRemoveAndDefine()
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefined('foo');
        $optionsResolver->setAllowedValues('foo', 'bar');
        $optionsResolver->remove('foo');
        $optionsResolver->setDefined('foo');

        $this->assertNull($optionsResolver->getAllowedValues('foo'));
    }

    public function testGetAllowedTypesAfterRemove()
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefined('foo');
        $optionsResolver->setAllowedTypes('foo', 'bar');
        $optionsResolver->remove('foo');

        $this->setExpectedException('Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException');

        $optionsResolver->getAllowedTypes('foo');
    }

    public function testGetAllowedTypesAfterRemoveAndDefine()
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefined('foo');
        $optionsResolver->setAllowedTypes('foo', 'bar');
        $optionsResolver->remove('foo');
        $optionsResolver->setDefined('foo');

        $this->assertNull($optionsResolver->getAllowedTypes('foo'));
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

    public function testGetDescriptionAfterClear()
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefined('foo');
        $optionsResolver->setDescription('foo', 'Foo');
        $optionsResolver->clear();

        $this->setExpectedException('Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException');

        $optionsResolver->getDescription('foo');
    }

    public function testGetAllowedValuesAfterClear()
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefined('foo');
        $optionsResolver->setAllowedValues('foo', 'bar');
        $optionsResolver->clear();

        $this->setExpectedException('Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException');

        $optionsResolver->getAllowedValues('foo');
    }

    public function testGetAllowedTypesAfterClear()
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefined('foo');
        $optionsResolver->setAllowedTypes('foo', 'bar');
        $optionsResolver->clear();

        $this->setExpectedException('Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException');

        $optionsResolver->getAllowedTypes('foo');
    }
}
