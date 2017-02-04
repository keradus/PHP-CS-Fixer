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

namespace PhpCsFixer\Fixer\Phpdoc;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\OptionsResolver;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Graham Campbell <graham@alt-three.com>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class GeneralPhpdocAnnotationRemoveFixer extends AbstractFixer implements ConfigurationDefinitionFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function configure(array $configuration = null)
    {
        if (is_array($configuration) && count($configuration) && !array_key_exists('annotations', $configuration)) {
            @trigger_error(
                'Passing annotations at the root of the configuration is deprecated and will not be supported in 3.0, use "annotations" => array(...) option.',
                E_USER_DEPRECATED
            );

            $configuration = array('annotations' => $configuration);
        }

        parent::configure($configuration);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationDefinition()
    {
        $configurationDefinition = new OptionsResolver();

        return $configurationDefinition
            ->setDefault('annotations', array())
            ->setAllowedTypes('annotations', 'array')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function fix(\SplFileInfo $file, Tokens $tokens)
    {
        if (!count($this->configuration['annotations'])) {
            return;
        }

        foreach ($tokens as $token) {
            if (!$token->isGivenKind(T_DOC_COMMENT)) {
                continue;
            }

            $doc = new DocBlock($token->getContent());
            $annotations = $doc->getAnnotationsOfType($this->configuration['annotations']);

            // nothing to do if there are no annotations
            if (empty($annotations)) {
                continue;
            }

            foreach ($annotations as $annotation) {
                $annotation->remove();
            }

            $token->setContent($doc->getContent());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new FixerDefinition(
            'Configured annotations should be omitted from phpdocs.',
            array(
                new CodeSample(
                    '<?php
/**
 * @internal
 * @author someone
 */
function foo() {}',
                    array('author')
                ),
            ),
            null,
            'Array of not wanted annotations could be configured, eg `[\'@author\']`.',
            $this->getDefaultConfiguration()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        // must be run before the PhpdocSeparationFixer, PhpdocOrderFixer,
        // PhpdocTrimFixer and PhpdocNoEmptyReturnFixer.
        return 10;
    }

    /**
     * {@inheritdoc}
     */
    public function isCandidate(Tokens $tokens)
    {
        return $tokens->isTokenKindFound(T_DOC_COMMENT);
    }
}
