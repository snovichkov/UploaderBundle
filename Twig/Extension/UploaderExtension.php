<?php

namespace CyberApp\UploaderBundle\Twig\Extension;

use CyberApp\UploaderBundle\Exception\UnsupportedException;

use Symfony\Component\DependencyInjection\ContainerInterface;

class UploaderExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    public $container;

    /**
     * Extension constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'uploader_is_uploaded_file' => new \Twig_Function_Method($this, 'isUploadedFile', array('is_safe' => array('html'))),
        );
    }

    /**
     * Check if file uploaded
     *
     * @param string $endpoint Configuration id
     * @param string $file     File name
     *
     * @throws UnsupportedException
     *
     * @return boolean
     */
    public function isUploadedFile($endpoint, $file)
    {
        $config = $this->container->getParameter('oneup_uploader.config.' . $endpoint)['storage'];

        if (isset($config['service']) && $config['service']) {
            throw new UnsupportedException('Custom providers of storage are not supported');
        }

        if ('filesystem' === $config['type']) {
            $directory = sprintf('%s/../web/uploads/%s', $this->container->getParameter('kernel.root_dir'), $endpoint);

            if (isset($config['directory']) && $config['directory']) {
                $directory = rtrim($config['directory'], '\\/');
            }

            return $this->container->get('filesystem')->exists($directory . DIRECTORY_SEPARATOR . $file);
        }

        if ('gaufrette' === $config['type']) {
            return $this->container->get($config['filesystem'])->has($file);
        }

        throw new UnsupportedException(sprintf('Storage type %s is not supported', $config['type']));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'cyber_app.twig.extension.uploader';
    }
}
