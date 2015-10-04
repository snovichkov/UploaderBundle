<?php

namespace CyberApp\UploaderBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\PreUpdateEventArgs;

use CyberApp\UploaderBundle\Exception\UnsupportedException;

class RemoveListener extends BaseListener
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return ['preUpdate', ];
    }

    /**
     * Handle pre update event
     *
     * @param PreUpdateEventArgs $args Event arguments
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $annotations = $this
            ->container
            ->get('cyber_app.metadata.reader')
            ->getUploadebleFieldsAnnotations($args->getEntity())
        ;

        if (0 === count($annotations)) {
            return ;
        }

        foreach ($annotations as $field => $annotation) {
            if (! ($args->hasChangedField($field) && $args->getOldValue($field))) {
                continue;
            }

            $config = $this->container->getParameter('oneup_uploader.config.' . $annotation->endpoint);

            if ($annotation->multiple) {
                $files = array_diff((array)$args->getOldValue($field), (array)$args->getNewValue($field));
                foreach ($files as $file) {
                    $this->unlink($annotation->endpoint, $config['storage'], $file);
                }
                continue;
            }

            if ($args->getOldValue($field)) {
                $this->unlink($annotation->endpoint, $config['storage'], $args->getOldValue($field));
            }
        }
    }

    /**
     * Unlink file
     *
     * @param string $endpoint An endpoint id
     * @param array  $config   Storage configuration
     * @param string $file     File name
     *
     * @throws UnsupportedException
     */
    protected function unlink($endpoint, array $config, $file)
    {
        if (isset($config['service']) && $config['service']) {
            throw new UnsupportedException('Custom providers of storage are not supported');
        }

        switch ($config['type']) {
            case 'filesystem':
                $directory = sprintf('%s/../web/uploads/%s', $this->container->getParameter('kernel.root_dir'),
                    $endpoint);

                if (isset($config['directory']) && $config['directory']) {
                    $directory = rtrim($config['directory'], '\\/');
                }

                $this->container->get('filesystem')->remove($directory . DIRECTORY_SEPARATOR . $file);
                break;

            case 'gaufrette':
                $fileSystem = $this->container->get($config['filesystem']);
                if ($fileSystem->has($file)) {
                    $fileSystem->delete($file);
                }
                break;

            default:
                throw new UnsupportedException(sprintf('Storage type %s is not supported', $config['type']));
        }
    }
}
