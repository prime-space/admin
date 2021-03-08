<?php namespace App\Annotation;

/** @Annotation */
class AccessRule
{
    public $name;
    public $queryIdKey;

    public function __construct(array $arguments)
    {
        $this->name = $arguments['name'] ?? '';
        $this->queryIdKey = $arguments['queryIdKey'] ?? '';
    }
}
