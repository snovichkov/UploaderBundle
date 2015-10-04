<?php

namespace CyberApp\UploaderBundle\Form\Type;

use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\Form\FormTypeGuesserInterface;

use CyberApp\UploaderBundle\Metadata\MetadataReader;

class UploaderTypeGuesser implements FormTypeGuesserInterface
{
    /**
     * @var MetadataReader
     */
    protected $metadataReader;

    /**
     * Constructor
     *
     * @param MetadataReader $metadataReader
     */
    public function __construct(MetadataReader $metadataReader)
    {
        $this->metadataReader = $metadataReader;
    }

    /**
     * {@inheritdoc}
     */
    public function guessType($class, $property)
    {
        if (null === ($annotation = $this->metadataReader->getUploadebleFieldAnnotation($class, $property))) {
            return null;
        }

        return new TypeGuess('uploader', [
            'endpoint' => $annotation->endpoint,
            'uri_prefix' => $annotation->uriPrefix,
            'multiple' => $annotation->multiple,
        ], Guess::VERY_HIGH_CONFIDENCE);
    }

    /**
     * {@inheritdoc}
     */
    public function guessRequired($class, $property)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function guessMaxLength($class, $property)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function guessPattern($class, $property)
    {
    }
}
