<?php

namespace CyberApp\UploaderBundle\Mapping\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class UploadableField extends Annotation
{
    /**
     * @Required()
     * @var string
     */
    public $endpoint;

    /**
     * @Required()
     * @var string
     */
    public $uriPrefix;

    /**
     * @var boolean
     */
    public $multiple = false;
}
