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

namespace PhpCsFixer\Fixer\FunctionNotation;

use PhpCsFixer\AbstractFunctionReferenceFixer;
use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\DocBlock\Line;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class AddMissingArgumentTypeFixer extends AbstractFunctionReferenceFixer
{
    /**
     * {@inheritdoc}
     */
    public function isCandidate(Tokens $tokens)
    {
        return PHP_VERSION_ID >= 70000 && $tokens->isTokenKindFound(T_FUNCTION);
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
        for ($index = 0, $limit = $tokens->count(); $index < $limit; ++$index) {
            $token = $tokens[$index];

            if (!$token->isGivenKind(T_DOC_COMMENT)) {
                continue;
            }

            if (1 === preg_match('/inheritdoc/i', $token->getContent())) {
                continue;
            }

            $index = $tokens->getNextMeaningfulToken($index);

            if (null === $index) {
                return;
            }

            while ($tokens[$index]->isGivenKind(array(
                T_PRIVATE,
                T_PROTECTED,
                T_PUBLIC,
                T_STATIC,
                T_VAR,
            ))) {
                $index = $tokens->getNextMeaningfulToken($index);
            }

            if (!$tokens[$index]->isGivenKind(T_FUNCTION)) {
                continue;
            }

            $openIndex = $tokens->getNextTokenOfKind($index, array('('));
            $index = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openIndex);

            $arguments = array();
            $argumentsIndexes = array();

            foreach ($this->getArguments($tokens, $openIndex, $index) as $start => $end) {
                $argumentInfo = $this->prepareArgumentInformation($tokens, $start, $end);
                if ('' === $argumentInfo['type']) {
                    $arguments[$argumentInfo['name']] = $argumentInfo;
                    $argumentsIndexes[$argumentInfo['name']] = [$start, $end];
                }
            }

            $doc = new DocBlock($token->getContent());

            foreach ($doc->getAnnotationsOfType('param') as $annotation) {
                $pregMatched = preg_match('/^[^$]+(\$\w+).*$/s', $annotation->getContent(), $matches);

                if (1 !== $pregMatched) {
                    continue;
                }

                $name = $matches[1];
                $types = $annotation->getTypes();
                $nullable = false;

                if (!isset($arguments[$name]) || 0 === count($types) || 2 < count($types)) {
                    continue;
                }

                if (2 === count($types)) {
                    $types = array_filter($types, function ($type) { return 'null' !== strtolower($type); });
                    if (2 === count($types)) {
                        continue;
                    }
                    $nullable = true;
                }

                $type = $types[0];

                if ('mixed' === $type) {
                    continue;
                }

                $arguments[$name]['type'] = $type;
                if ($nullable && '' === $arguments[$name]['default']) {
                    $arguments[$name]['default'] = 'null';
                }
            }

            foreach (array_reverse($argumentsIndexes) as $name => $indexes) {
                $info = $arguments[$name];

                if ('' === $info['type']) {
                    continue;
                }

                if ($info['default']) {
                    $tokens->insertAt(
                        $indexes[1] + 1,
                        [
                            new Token([T_WHITESPACE, ' ']),
                            new Token('='),
                            new Token([T_WHITESPACE, ' ']),
                            new Token([T_STRING, $info['default']]),
                        ]
                    );
                }

                $toInsert = [
                    new Token([T_STRING, $info['type']]),
                    new Token([T_WHITESPACE, ' ']),
                ];

                if ($tokens[$indexes[0]]->isWhitespace()) {
                    $toInsert = array_reverse($toInsert);
                }

                $tokens->insertAt(
                    $indexes[0],
                    $toInsert
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'TODO.';
    }

    /**
     * @param Tokens $tokens
     * @param int    $start
     * @param int    $end
     *
     * @return array
     */
    private function prepareArgumentInformation(Tokens $tokens, $start, $end)
    {
        $info = array(
            'default' => '',
            'name' => '',
            'type' => '',
        );

        $sawName = false;
        $sawEq = false;

        for ($index = $start; $index <= $end; ++$index) {
            $token = $tokens[$index];

            if ($token->isComment() || $token->isWhitespace()) {
                continue;
            }

            if ($token->isGivenKind(T_VARIABLE)) {
                $sawName = true;
                $info['name'] = $token->getContent();
                continue;
            }

            if ($token->equals('=')) {
                $sawEq = true;
                continue;
            }

            if (!$sawName) {
                $info['type'] .= $token->getContent();
            } elseif ($sawEq) {
                $info['default'] .= $token->getContent();
            }
        }

        return $info;
    }
}
