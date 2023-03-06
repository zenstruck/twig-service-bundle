<?php

/*
 * This file is part of the zenstruck/twig-service-bundle package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Twig\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zenstruck\Twig\Service\TwigFunctionRuntime;
use Zenstruck\Twig\Service\TwigServiceRuntime;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[AsCommand('zenstruck:twig-service:list', 'List available Twig functions/services.')]
final class ListCommand extends Command
{
    public function __construct(private TwigServiceRuntime $services, private TwigFunctionRuntime $functions)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $functions = $this->functions->functions();
        $services = $this->services->all();

        unset($services[TwigServiceRuntime::PARAMETER_BAG]);

        if (!$services && !$functions) {
            $io->warning('No twig services or functions registered.');

            return self::SUCCESS;
        }

        if ($functions) {
            $io->section('Available Functions/Filters');
            $io->comment("As function: call with <info>fn('{alias}', {...args})</info> or <info>fn_{alias}({...args})</info>");
            $io->comment("As filter: use as <info>{value}|fn('{alias}', {...args})</info> or <info>{value}|fn_{alias}({...args})</info>");
            $io->table(
                ['Alias', 'Callable'],
                \array_map(function(string|array $value, string $key) {
                    if (\is_string($value)) {
                        return [$key, $value];
                    }

                    $class = $value[0];

                    if (!\class_exists($class) && !\interface_exists($class)) {
                        $class = '@'.$class;
                    }

                    return [
                        $key,
                        \sprintf(
                            '%s%s%s()',
                            $class,
                            $this->functions->container()->has($value[0]) ? '->' : '::',
                            $value[1]
                        ),
                    ];
                }, $functions, \array_keys($functions))
            );
        }

        if ($services) {
            $io->section('Available Services');
            $io->comment("Access via <info>service('{alias}')</info> or <info>service_{alias}()</info>");
            $io->comment("If invokable, use as <info>{value}|service('{alias}', {...args})</info> or <info>{value}|service_{alias}({...args})</info>");
            $io->table(
                ['Alias', 'Service', 'Invokable?'],
                \array_map(
                    static function(string $service, string $alias) {
                        $isClass = \class_exists($service) || \interface_exists($service);

                        return [
                            $alias,
                            $service,
                            $isClass ? (\method_exists($service, '__invoke') ? 'yes' : 'no') : '?',
                        ];
                    },
                    $services, \array_keys($services)
                )
            );
        }

        return self::SUCCESS;
    }
}
