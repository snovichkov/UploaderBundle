<?php

namespace CyberApp\UploaderBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use CyberApp\UploaderBundle\DependencyInjection\Compiler\BundleCompilerPass;

class CyberAppUploaderBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new BundleCompilerPass());

        parent::build($container);
    }
}
