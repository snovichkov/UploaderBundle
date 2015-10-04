<?php

namespace CyberApp\UploaderBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class MultipleTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (empty($value)) {
            return [];
        }

        if (is_scalar($value)) {
            return [$value];
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        return $this->transform($value);
    }
}