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

use PhpCsFixer\AbstractFunctionReferenceFixer;
use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\DocBlock\Line;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class PhpdocAddMissingParamAnnotationFixer extends AbstractFunctionReferenceFixer
{
    private $config;
    // TODO: proper validation
    public function configure(array $config = null) {
        if (null === $config) {
            $this->config = [
                'only_untyped' => true,
            ];

            return;
        }

        $this->config = $config;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(Tokens $tokens)
    {
        return $tokens->isTokenKindFound(T_DOC_COMMENT);
    }

    /**
     * {@inheritdoc}
     */
    public function isRisky()
    {
        return false;
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

            foreach ($this->getArguments($tokens, $openIndex, $index) as $start => $end) {
                $argumentInfo = $this->prepareArgumentInformation($tokens, $start, $end);
                $arguments[$argumentInfo['name']] = $argumentInfo;
            }

            $doc = new DocBlock($token->getContent());

            foreach ($doc->getAnnotationsOfType('param') as $annotation) {
                $pregMatched = preg_match('/^[^$]+(\$\w+).*$/s', $annotation->getContent(), $matches);

                if (1 !== $pregMatched) {
                    continue;
                }

                unset($arguments[$matches[1]]);
            }

            if (!count($arguments)) {
                continue;
            }

            $lines = $doc->getLines();
            $lastLine = array_pop($lines);

            preg_match('/^(\s*).*$/', $lastLine->getContent(), $matches);
            $indent = $matches[1];

            if ($this->config['only_untyped']) {
                $arguments = array_filter($arguments, function (array $argument) {
                    return '' === $argument['type'];
                });
            }

            foreach ($arguments as $argument) {
                $type = $argument['type'] ?: 'mixed';
                if ('?' !== $type[0] && 'null' === strtolower($argument['default'])) {
                    $type = 'null|'.$type;
                }

                $lines[] = new Line(sprintf(
                    '%s* @param %s %s%s',
                    $indent,
                    $type,
                    $argument['name'],
                    "\n"
                ));
            }

            $lines[] = $lastLine;

            $token->setContent(implode('', $lines));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'Phpdoc should contain @param for all params.';
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
