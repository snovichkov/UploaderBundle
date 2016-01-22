<?php

namespace CyberApp\UploaderBundle\Uploader\Namer;

use Oneup\UploaderBundle\Uploader\File\FileInterface;
use Oneup\UploaderBundle\Uploader\Naming\NamerInterface;

class UniqidNamer implements NamerInterface
{
    /**
     * @inheritdoc
     */
    public function name(FileInterface $file)
    {
        $name = uniqid();

        return sprintf('%s/%s/%s.%s', substr($name, 0, 2), substr($name, 2, 2), $name, $file->getExtension());
    }
}