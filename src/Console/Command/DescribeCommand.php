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

namespace PhpCsFixer\Console\Command;

use PhpCsFixer\ConfigurationException\UnallowedFixerConfigurationException;
use PhpCsFixer\Differ\SebastianBergmannDiffer;
use PhpCsFixer\FixerDescriptionAwareInterface;
use PhpCsFixer\FixerFactory;
use PhpCsFixer\FixerInterface;
use PhpCsFixer\RuleSet;
use PhpCsFixer\StdinFileInfo;
use PhpCsFixer\Tokenizer\Tokens;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
final class DescribeCommand extends Command
{
    /**
     * @var string[]
     */
    private $setNames;

    /**
     * @var array<string, FixerInterface>
     */
    private $fixers;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('describe')
            ->setDefinition(
                array(
                    new InputArgument('name', InputArgument::REQUIRED, 'Name of rule / set.'),
                )
            )
            ->setDescription('Describe rule / ruleset.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        try {
            if ('@' === $name[0]) {
                $this->describeSet($input, $output, $name);

                return;
            }

            $this->describeRule($input, $output, $name);
        } catch (DescribeNameNotFoundException $e) {
            $alternative = $this->getAlternative($e->getType(), $name);
            $this->describeList($input, $output, $e->getType());

            throw new \InvalidArgumentException(sprintf(
                '%s %s not found.%s',
                ucfirst($e->getType()), $name, null === $alternative ? '' : ' Did you mean "'.$alternative.'"?'
            ));
        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $name
     */
    private function describeRule(InputInterface $input, OutputInterface $output, $name)
    {
        $fixers = $this->getFixers();

        if (!isset($fixers[$name])) {
            throw new DescribeNameNotFoundException($name, 'rule');
        }

        $fixer = $fixers[$name];
        $description = $fixer instanceof FixerDescriptionAwareInterface ? $fixer->getFixerDescription() : null;

        $output->writeln(sprintf('<info>Description of %s rule.</info>', $name));
        $output->writeln($fixer->getDescription());
        $output->writeln('');

        if ($fixer->isRisky()) {
            $output->writeln('<error>Fixer applying this rule is risky.</error>');

            if ($description && $description->getRiskyDescription()) {
                $output->writeln($description->getRiskyDescription());
            }

            $output->writeln('');
        }

        if ($this->isFixerConfigurable($fixer)) {
            $output->writeln('<comment>Fixer is configurable.</comment>');

            if ($description && $description->getConfigurationDescription()) {
                $output->writeln($description->getConfigurationDescription());
            }

            if ($description && $description->getDefaultConfiguration()) {
                $output->writeln(sprintf('Default configuration: <comment>%s</comment>.', $this->arrayToText($description->getDefaultConfiguration())));
            }

            $output->writeln('');
        }

        if ($description && $description->getCodeSamples()) {
            $output->writeln('Fixing examples:');
            $differ = new SebastianBergmannDiffer();

            foreach ($description->getCodeSamples() as $index => $codeSample) {
                $old = $codeSample[0];
                $tokens = Tokens::fromCode($old);
                $fixer->configure($codeSample[1]);
                $fixer->fix(new StdinFileInfo(), $tokens);
                $new = $tokens->generateCode();
                $diff = $differ->diff($old, $new);

                if (null === $codeSample[1]) {
                    $output->writeln(sprintf(' * Example #%d.', $index + 1));
                } else {
                    $output->writeln(sprintf(' * Example #%d. Fixing with configuration: <comment>%s</comment>.', $index + 1, $this->arrayToText($codeSample[1])));
                }

                $output->writeln($this->prepareDiff($output->isDecorated(), $diff));
                $output->writeln('');
            }
        }

        if (!$description) {
            $output->writeln(sprintf('<question>This rule is not yet described, do you want to help us and describe it?</question>'));
            $output->writeln('Contribute at <comment>https://github.com/FriendsOfPHP/PHP-CS-Fixer</comment>');
            $output->writeln('');
        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $name
     */
    private function describeSet(InputInterface $input, OutputInterface $output, $name)
    {
        if (!in_array($name, $this->getSetNames(), true)) {
            throw new DescribeNameNotFoundException($name, 'set');
        }

        $ruleSet = new RuleSet(array($name => true));
        $rules = $ruleSet->getRules();
        ksort($rules);

        $fixers = $this->getFixers();

        $output->writeln(sprintf('<info>Description of %s set.</info>', $name));
        $output->writeln('');

        $help = '';

        foreach ($rules as $rule => $config) {
            $help .= sprintf(
                " * <info>%s</info>%s\n   | %s\n%s\n",
                $rule,
                $fixers[$rule]->isRisky() ? ' <error>[risky]</error>' : '',
                $fixers[$rule]->getDescription(),
                true !== $config ? sprintf("   <comment>| Configuration: %s</comment>\n", $this->arrayToText($config)) : ''
            );
        }

        $output->write($help);
    }

    /**
     * @param FixerInterface $fixer
     *
     * @return bool
     */
    private function isFixerConfigurable(FixerInterface $fixer)
    {
        try {
            $fixer->configure(array());

            return true;
        } catch (UnallowedFixerConfigurationException $e) {
            return false;
        } catch (\Exception $e) {
        }

        return true;
    }

    private function arrayToText(array $data)
    {
        static $replaces = array(
            '#\r|\n#' => '',
            '#\s{1,}#' => ' ',
            '#array\s*\((.*)\)#s' => '[$1]',
            '#\[\s+#' => '[',
            '#,\s*\]#' => ']',
            '#\d+\s*=>\s*#' => '',
        );

        return preg_replace(
            array_keys($replaces),
            array_values($replaces),
            var_export($data, true)
        );
    }

    /**
     * @param bool   $isDecoratedOutput
     * @param string $diff
     *
     * @return string
     */
    private function prepareDiff($isDecoratedOutput, $diff)
    {
        $template = "<comment>   ---------- begin diff ----------</comment>\n%s\n<comment>   ----------- end diff -----------</comment>";
        $diff = implode(
            PHP_EOL,
            array_map(
                function ($string) {
                    $string = preg_replace('/^(\+.*)/', '<fg=green>\1</>', $string);
                    $string = preg_replace('/^(\-.*)/', '<fg=red>\1</>', $string);
                    $string = preg_replace('/^(@.*)/', '<fg=cyan>\1</>', $string);

                    return '   '.$string;
                },
                preg_split("#\n\r|\n#", OutputFormatter::escape(rtrim($diff)))
            )
        );

        return sprintf($template, $diff);
    }

    /**
     * @return array<string, FixerInterface>
     */
    private function getFixers()
    {
        if (null !== $this->fixers) {
            return $this->fixers;
        }

        $fixerFactory = new FixerFactory();
        $fixers = array();

        foreach ($fixerFactory->registerBuiltInFixers()->getFixers() as $fixer) {
            $fixers[$fixer->getName()] = $fixer;
        }

        $this->fixers = $fixers;
        ksort($this->fixers);

        return $this->fixers;
    }

    /**
     * @return string[]
     */
    private function getSetNames()
    {
        if (null !== $this->setNames) {
            return $this->setNames;
        }

        $set = new RuleSet();
        $this->setNames = $set->getSetDefinitionNames();
        sort($this->setNames);

        return $this->setNames;
    }

    /**
     * @param string $type 'rule'|'set'
     * @param string $name
     *
     * @return string|null
     */
    private function getAlternative($type, $name)
    {
        $other = null;
        $alternatives = 'set' === $type ? $this->getSetNames() : $this->getFixers();

        foreach ($alternatives as $alternative) {
            $distance = levenshtein($name, $alternative);
            if (3 > $distance) {
                $other = $alternative;

                break;
            }
        }

        return $other;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $type   'rule'|'set'
     */
    private function describeList(InputInterface $input, OutputInterface $output, $type)
    {
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $describe = array(
                'set' => $this->getSetNames(),
                'rules' => $this->getFixers(),
            );
        } elseif ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $describe = 'set' === $type ? array('set' => $this->getSetNames()) : array('rules' => $this->getFixers());
        } else {
            return;
        }

        foreach ($describe as $list => $items) {
            $output->writeln(sprintf('<comment>Defined %s:</comment>', $list));
            foreach ($items as $name => $item) {
                $output->writeln(sprintf('- %s', is_string($name) ? $name : $item));
            }
        }
    }
}
