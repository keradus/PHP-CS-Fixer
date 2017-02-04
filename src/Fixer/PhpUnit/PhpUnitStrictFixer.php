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

namespace PhpCsFixer\Fixer\PhpUnit;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\OptionsResolver;
use PhpCsFixer\Tokenizer\Tokens;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class PhpUnitStrictFixer extends AbstractFixer implements ConfigurationDefinitionFixerInterface
{
    private static $assertionMap = array(
        'assertAttributeEquals' => 'assertAttributeSame',
        'assertAttributeNotEquals' => 'assertAttributeNotSame',
        'assertEquals' => 'assertSame',
        'assertNotEquals' => 'assertNotSame',
    );

    /**
     * {@inheritdoc}
     */
    public function configure(array $configuration = null)
    {
        if (is_array($configuration) && count($configuration) && !array_key_exists('assertions', $configuration)) {
            @trigger_error(
                'Passing assertions at the root of the configuration is deprecated and will not be supported in 3.0, use "assertions" => array(...) option instead.',
                E_USER_DEPRECATED
            );

            $configuration = array('assertions' => $configuration);
        }

        parent::configure($configuration);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationDefinition()
    {
        $map = self::$assertionMap;
        $configurationDefinition = new OptionsResolver();

        return $configurationDefinition
            ->setDefault('assertions', array(
                'assertAttributeEquals',
                'assertAttributeNotEquals',
                'assertEquals',
                'assertNotEquals',
            ))
            ->setAllowedTypes('assertions', 'array')
            ->setNormalizer('assertions', function (Options $options, $value) use ($map) {
                foreach ($value as $method) {
                    if (!array_key_exists($method, $map)) {
                        throw new InvalidOptionsException(sprintf(
                            'Configured method "%s" cannot be fixed by this fixer.',
                            $method
                        ));
                    }
                }

                return $value;
            })
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function fix(\SplFileInfo $file, Tokens $tokens)
    {
        foreach ($this->configuration['assertions'] as $methodBefore) {
            $methodAfter = self::$assertionMap[$methodBefore];

            for ($index = 0, $limit = $tokens->count(); $index < $limit; ++$index) {
                $sequence = $tokens->findSequence(
                    array(
                        array(T_VARIABLE, '$this'),
                        array(T_OBJECT_OPERATOR, '->'),
                        array(T_STRING, $methodBefore),
                        '(',
                    ),
                    $index
                );

                if (null === $sequence) {
                    break;
                }

                $sequenceIndexes = array_keys($sequence);
                $tokens[$sequenceIndexes[2]]->setContent($methodAfter);

                $index = $sequenceIndexes[3];
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new FixerDefinition(
            'PHPUnit methods like `assertSame` should be used instead of `assertEquals`.',
            array(
                new CodeSample(
'<?php
final class MyTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeTest()
    {
        $this->assertAttributeEquals(a(), b());
        $this->assertAttributeNotEquals(a(), b());
        $this->assertEquals(a(), b());
        $this->assertNotEquals(a(), b());
    }
}
'
                ),
            ),
            null,
            'Configure which of the following functions should be replaced `assertAttributeEquals`, `assertAttributeNotEquals`, `assertEquals`, `assertNotEquals`',
            $this->getDefaultConfiguration(),
            'Risky when the any of functions are overridden.'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isCandidate(Tokens $tokens)
    {
        return $tokens->isTokenKindFound(T_STRING);
    }

    /**
     * {@inheritdoc}
     */
    public function isRisky()
    {
        return true;
    }
}
