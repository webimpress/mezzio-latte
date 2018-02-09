<?php

declare(strict_types=1);

namespace Zend\Expressive\Latte;

use Zend\Expressive\Template\TemplateRendererInterface;

class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates' => $this->getTemplates(),
            'latte' => $this->getLatte(),
        ];
    }

    public function getDependencies() : array
    {
        return [
            'aliases' => [
                TemplateRendererInterface::class => LatteRenderer::class,
            ],
            'invokables' => [
                Macro\Path::class => Macro\Path::class,
                Macro\Url::class => Macro\Url::class,
            ],
            'factories' => [
                Filter\ServerUrlHelper::class => Filter\ServerUrlHelperFactory::class,
                Filter\UrlHelper::class => Filter\UrlHelperFactory::class,
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

    public function getLatte() : array
    {
        return [
            'filters' => [
                'urlHelper' => Filter\UrlHelper::class,
                'serverUrlHelper' => Filter\ServerUrlHelper::class,
            ],
            'macros' => [
                'path' => Macro\Path::class,
                'url' => Macro\Url::class,
            ],
        ];
    }
}
