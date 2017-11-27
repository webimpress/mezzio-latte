<?php

namespace Zend\Expressive\Latte;

use Zend\Expressive\Template\TemplateRendererInterface;

class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates' => $this->getTemplates(),
        ];
    }

    public function getDependencies() : array
    {
        return [
            'aliases' => [
                TemplateRendererInterface::class => LatteRenderer::class,
            ],
            'factories' => [
                LatteRenderer::class => LatteRendererFactory::class,
                MultiplePathLoaderInterface::class => MultipleFileLoaderFactory::class,
            ],
        ];
    }

    public function getTemplates() : array
    {
        return [
            'extension' => 'latte',
            'paths' => [],
        ];
    }
}
