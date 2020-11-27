<?php

declare(strict_types=1);

namespace Webimpress\Mezzio\Latte;

use Latte\Engine as LatteEngine;
use Latte\Macros\MacroSet;
use Psr\Container\ContainerInterface;
use Webimpress\Mezzio\Latte\Exception\InvalidMacroException;

use function get_class;
use function is_array;
use function is_int;
use function is_object;
use function is_string;
use function sprintf;

class LatteRendererFactory
{
    /**
     * @throws InvalidMacroException
     */
    public function __invoke(ContainerInterface $container) : LatteRenderer
    {
        $config = $container->get('config')['latte'] ?? [];

        $engine = new LatteEngine();
        if (isset($config['cache_dir'])) {
            $engine->setTempDirectory($config['cache_dir']);
        }

        if (isset($config['filters']) && is_array($config['filters'])) {
            foreach ($config['filters'] as $name => $callback) {
                if (is_int($name)) {
                    $name = null;
                }

                if (is_string($callback) && $container->has($callback)) {
                    $callback = $container->get($callback);
                }

                $engine->addFilter($name, $callback);
            }
        }

        if (isset($config['macros']) && is_array($config['macros'])) {
            $set = new MacroSet($engine->getCompiler());

            foreach ($config['macros'] as $name => $macro) {
                if (is_string($macro) && $container->has($macro)) {
                    $macro = $container->get($macro);
                }

                if (is_object($macro)) {
                    if (! $macro instanceof MacroInterface) {
                        throw new InvalidMacroException(sprintf(
                            'Macro class %s must implement %s',
                            get_class($macro),
                            MacroInterface::class
                        ));
                    }

                    $macro = [
                        [$macro, 'begin'],
                        [$macro, 'end'],
                        [$macro, 'attr'],
                        $macro->flag(),
                    ];
                }

                $set->addMacro($name, ...$macro);
            }
        }

        $loader = $container->get(MultiplePathLoaderInterface::class);
        $engine->setLoader($loader);

        return new LatteRenderer($engine);
    }
}
