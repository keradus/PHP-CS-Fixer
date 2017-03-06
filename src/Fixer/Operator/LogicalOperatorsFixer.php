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

namespace PhpCsFixer\Fixer\Operator;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Haralan Dobrev <hkdobrev@gmail.com>
 */
class LogicalOperatorsFixer extends AbstractFixer implements ConfigurableFixerInterface
{
    /**
     * @var array<string, bool>
     */
    private static $defaultConfiguration = array(
        // Use and, or instead of &&, ||
        'use_keywords' => false,
    );

    /**
     * {@inheritdoc}
     */
    public function configure(array $configuration = null)
    {
        if (null === $configuration) {
            $this->config = self::$defaultConfiguration;

            return;
        }

        $configuration = array_merge(self::$defaultConfiguration, $configuration);

        foreach ($configuration as $item => $value) {
            if (!array_key_exists($item, self::$defaultConfiguration)) {
                throw new InvalidFixerConfigurationException($this->name(), sprintf('Unknown configuration item "%s", expected any of "%s".', $item, implode(', ', array_keys(self::$defaultConfiguration))));
            }

            if (!is_bool($value)) {
                throw new InvalidFixerConfigurationException($this->name(), sprintf('Configuration value for item "%s" must be a bool, got "%s".', $item, is_object($value) ? get_class($value) : gettype($value)));
            }
        }

        $this->config = $configuration;
    }

        /**
         * {@inheritdoc}
         */
        public function getDefinition()
        {
            return new FixerDefinition(
                'Use `&&` and `||` logical operators instead of `and` and `or`',
                array(
                    new CodeSample(
    '<?php

    if ($a == "foo" && ($b == "bar" || $c == "baz")) {
    }'
                    ),
                    new CodeSample(
    '<?php

    if ($a == "foo"and ($b == "bar" or $c == "baz")) {
    }',
                        array('use_keywords' => true)
                    ),
                ),
                null,
                'Configure to reverse it to use `and` and `or` instead of `&&` and `||`',
                self::$defaultConfiguration
            );
        }

    /**
     * {@inheritdoc}
     */
    public function isCandidate(Tokens $tokens)
    {
        if ($this->config['use_keywords']) {
            return $tokens->isTokenKindFound(T_BOOLEAN_AND) || $tokens->isTokenKindFound(T_BOOLEAN_OR);
        }

        return $tokens->isTokenKindFound(T_LOGICAL_AND) || $tokens->isTokenKindFound(T_LOGICAL_OR);
    }

    /**
     * {@inheritdoc}
     */
    public function isRisky()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function fix(\SplFileInfo $file, Tokens $tokens)
    {
        $sourceAnd = array(T_LOGICAL_AND, 'and');
        $targetAnd = array(T_BOOLEAN_AND, '&&');
        $sourceOr = array(T_LOGICAL_OR, 'or');
        $targetOr = array(T_BOOLEAN_OR, '||');

        if ($this->config['use_keywords']) {
            list($sourceAnd, $sourceOr, $targetAnd, $targetOr) = array($targetAnd, $targetOr, $sourceAnd, $sourceOr);
        }

        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind($sourceAnd[0]) && !$token->isGivenKind($sourceOr[0])) {
                continue;
            }

            if ($token->isGivenKind($sourceAnd[0])) {
                $tokens->overrideAt($index, $targetAnd);
            } elseif ($token->isGivenKind($sourceOr[0])) {
                $tokens->overrideAt($index, $targetOr);
            }
        }
    }
}
