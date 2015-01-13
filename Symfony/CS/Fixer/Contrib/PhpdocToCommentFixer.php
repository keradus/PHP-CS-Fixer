<?php

/*
 * This file is part of the PHP CS utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\CS\Fixer\Contrib;

use Symfony\CS\AbstractFixer;
use Symfony\CS\Tokenizer\Tokens;

/**
 * @author Ceeram <ceeram@cakephp.org>
 */
class PhpdocToCommentFixer extends AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function fix(\SplFileInfo $file, $content)
    {
        $tokens = Tokens::fromCode($content);

        foreach ($tokens->findGivenKind(T_DOC_COMMENT) as $index => $token) {
            $nextIndex = $tokens->getNextMeaningfulToken($index);

            // skip if there is no next token or if next token is block end `}`
            if (null === $nextIndex || $tokens[$nextIndex]->equals('}')) {
                continue;
            }

            if ($this->isStructuralElement($tokens[$nextIndex])) {
                continue;
            }

            if ($tokens[$nextIndex]->isGivenkind(T_FOREACH) && $this->isValidForeach($tokens, $index)) {
                continue;
            }

            $token->setContent('/*'.ltrim($token->getContent(), '/*'));
        }

        return $tokens->generateCode();
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'Docblocks should only be used on structural elements.';
    }

    /**
     * Check if token is a structural element
     * @see http://www.phpdoc.org/docs/latest/glossary.html#term-structural-elements
     *
     * @param $token
     * @return bool
     */
    private function isStructuralElement($token)
    {
        $skip = array(
            T_PRIVATE,
            T_PROTECTED,
            T_PUBLIC,
            T_FUNCTION,
            T_ABSTRACT,
            T_CONST,
            T_NAMESPACE,
            T_REQUIRE,
            T_REQUIRE_ONCE,
            T_INCLUDE,
            T_INCLUDE_ONCE,
        );

        if ($token->isClassy() || $token->isGivenKind($skip)) {
            return true;
        }

        return false;
    }

    /**
     * Checks foreach statements for correct docblock usage.
     *
     * @param $tokens
     * @param $index
     * @return bool
     */
    private function isValidForeach($tokens, $index)
    {
        $startIndex = $index;
        $endIndex = $tokens->getNextTokenOfKind($startIndex, array(')'));
        while ($startIndex < $endIndex) {
            ++$startIndex;
            if (!$tokens[$startIndex]->isGivenkind(T_AS)) {
                continue;
            }

            /*
             * Is the $value documented in docblock: foreach($array as $value)
             * or
             * Is the $key documented in docblock: foreach($array as $key => $value)
            */
            $nextMeaningful = $tokens->getNextMeaningfulToken($startIndex);
            if (strpos($tokens[$index]->getContent(), $tokens[$nextMeaningful]->getContent()) !== false) {
                return true;
            }

            // Is the $value documented in docblock: ($array as $key => $value)
            $nextMeaningful = $tokens->getNextMeaningfulToken($tokens->getNextMeaningfulToken($nextMeaningful));
            if (strpos($tokens[$index]->getContent(), $tokens[$nextMeaningful]->getContent()) !== false) {
                return true;
            }
        }

        return false;
    }
}
