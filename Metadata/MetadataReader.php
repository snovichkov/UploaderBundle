<?php

namespace CyberApp\UploaderBundle\Metadata;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Annotations\AnnotationReader;

class MetadataReader
{
    const UPLOADABLE = 'CyberApp\UploaderBundle\Mapping\Annotation\Uploadable';

    const UPLOADEBLE_FILED = 'CyberApp\UploaderBundle\Mapping\Annotation\UploadableField';

    /**
     * @var array
     */
    protected $cache = [];

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var AnnotationReader
     */
    protected $annotationReader;

    /**
     * Constructor
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
        $this->annotationReader = new AnnotationReader();
    }

    /**
     * Get class name
     *
     * @param object|string $source An entity or class
     *
     * @return string
     */
    public function getClass($source)
    {
        if (is_string($source)) {
            return $source;
        }

        if (class_exists('\Doctrine\Common\Util\ClassUtils')) {
            return \Doctrine\Common\Util\ClassUtils::getClass($source);
        }

        return get_class($source);
    }

    /**
     * Get object manager for class
     *
     * @param string|object $source An class name or entity
     *
     * @return ObjectManager
     */
    public function getManager($source)
    {
        $source = $this->getClass($source);

        if (null === ($om = $this->registry->getManagerForClass($source))) {
            throw new \LogicException(sprintf('Could not get object manager for class %s', $source));
        }

        return $om;
    }

    /**
     * Get class metadata by class name
     *
     * @param string|object $source An class name or entity
     *
     * @return ClassMetadataInfo
     */
    public function getClassMetadata($source)
    {
        $source = $this->getClass($source);

        if (isset($this->cache[$source])) {
            return $this->cache[$source];
        }

        $this->cache[$source] = $this->getManager($source)->getMetadataFactory()->getMetadataFor($source);

        return $this->cache[$source];
    }

    /**
     * Check is uploadeble object
     *
     * @param object|string $source
     *
     * @return boolean
     */
    public function isUploadable($source)
    {
        $metadata = $this->getClassMetadata($this->getClass($source));

        return null !== $this->annotationReader->getClassAnnotation($metadata->getReflectionClass(), static::UPLOADABLE);
    }

    /**
     * Get annotations of uploadeble fields
     *
     * @param object|string $source An class name or entity
     *
     * @return array
     */
    public function getUploadebleFieldsAnnotations($source)
    {
        $source = $this->getClass($source);

        if (! $this->isUploadable($source)) {
            return [];
        }

        $metadata = $this->getClassMetadata($source);

        $annotations = [];
        foreach ($metadata->getReflectionProperties() as $property) {
            if (null !== ($annotation = $this->annotationReader->getPropertyAnnotation($property,
                    static::UPLOADEBLE_FILED))
            ) {
                $annotations[$property->getName()] = $annotation;
            }
        }

        return $annotations;
    }

    /**
     * Get annotation of uploadeble field
     *
     * @param object|string $source An class name or entity
     * @param string        $field  An field name
     *
     * @return array
     */
    public function getUploadebleFieldAnnotation($source, $field)
    {
        $source = $this->getClass($source);

        if (! $this->isUploadable($source)) {
            return null;
        }

        $metadata = $this->getClassMetadata($source);

        if (! $metadata->hasField($field)) {
            return null;
        }

        return $this->annotationReader
            ->getPropertyAnnotation($metadata->getReflectionProperty($field), static::UPLOADEBLE_FILED);
    }
}
