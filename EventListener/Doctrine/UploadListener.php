<?php

namespace CyberApp\UploaderBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

use Symfony\Component\PropertyAccess\PropertyAccess;

class UploadListener extends BaseListener
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return ['prePersist', 'preUpdate', ];
    }

    /**
     * Handle pre persist event
     *
     * @param LifecycleEventArgs $args Event arguments
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->preSave($args);
    }

    /**
     * Handle pre update event
     *
     * @param PreUpdateEventArgs $args Event arguments
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $this->preSave($args);
    }

    /**
     * Handle pre save event
     *
     * @param LifecycleEventArgs $args Event arguments
     */
    protected function preSave(LifecycleEventArgs $args)
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
            $config = $this->container->getParameter('oneup_uploader.config.' . $annotation->endpoint);
            if (! (isset($config['use_orphanage']) && $config['use_orphanage'])) {
                continue;
            }

            $value = (array) PropertyAccess::createPropertyAccessor()->getValue($args->getEntity(), $field);
            $value = array_filter($value, 'strlen');
            $value = array_map(function ($file) {
                return pathinfo($file, PATHINFO_BASENAME);
            }, $value);

            if (empty($value)) {
                continue;
            }

            $orphanageStorage = $this->container->get('oneup_uploader.orphanage.' . $annotation->endpoint);

            $files = [];
            foreach ($orphanageStorage->getFiles() as $file) {
                if (in_array($file->getBasename(), $value, true)) {
                    $files[] = $file;
                }
            }

            $orphanageStorage->uploadFiles($files);
        }
    }
}
