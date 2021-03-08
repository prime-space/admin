<?php namespace App\Form\Extension\Core\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class VuetifyIdsTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        return $value;
    }

    public function reverseTransform($value)
    {
        $value = trim($value);
        if (empty($value)) {
            return [];
        }
        $value = explode(',', $value);
        $value = array_map('intval', $value);

        return $value;
    }
}
