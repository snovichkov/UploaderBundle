<?php

namespace CyberApp\UploaderBundle\EventListener\Kernel;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oneup\UploaderBundle\Event\PostUploadEvent;

class ResponseListener implements EventSubscriberInterface
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * Listener constructor
     *
     * @property Router $router Router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return ['oneup_uploader.post_upload' => 'postUpload', ];
    }

    /**
     * On upload event
     *
     * @param PostUploadEvent $event
     */
    public function postUpload(PostUploadEvent $event)
    {
        $file = $event->getFile();

        $files = [[
            'size' => $file->getSize(),
            'name' => $file->getBasename(),
        ]];

        $config = $event->getConfig();
        if (isset($config['use_orphanage']) && $config['use_orphanage']) {
            $files[0]['url'] = $this->router->generate('view_orphanage_upload', [
                'endpoint' => $event->getType(),
                'file' => $file->getBasename(),
            ]);
        }

        $event->getResponse()['files'] = $files;
    }
}
