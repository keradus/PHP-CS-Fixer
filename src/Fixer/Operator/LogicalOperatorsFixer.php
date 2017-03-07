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
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Haralan Dobrev <hkdobrev@gmail.com>
 */
final class LogicalOperatorsFixer extends AbstractFixer
{
        /**
         * {@inheritdoc}
         */
        public function getDefinition()
        {
            return new FixerDefinition(
                'Use `&&` and `||` logical operators instead of `and` and `or`.',
                array(
                    new CodeSample(
    '<?php

    if ($a == "foo" && ($b == "bar" || $c == "baz")) {
    }'
                    ),
                ),
                null,
                null,
                null,
                'Risky, because you must double-check if using and/or with lower precedence was intentional'
            );
        }

    /**
     * {@inheritdoc}
     */
    public function isCandidate(Tokens $tokens)
    {
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
        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind(T_LOGICAL_AND) && !$token->isGivenKind(T_LOGICAL_OR)) {
                continue;
            }

            if ($token->isGivenKind(T_LOGICAL_AND)) {
                $tokens->overrideAt($index, array(T_BOOLEAN_AND, '&&'));
            } elseif ($token->isGivenKind(T_LOGICAL_OR)) {
                $tokens->overrideAt($index, array(T_BOOLEAN_OR, '||'));
            }
        }
    }
}
