<?php namespace App\Constraint;

use Symfony\Component\Validator\Constraint;

class Accuracy extends Constraint
{
    public $message = 'wrong accuracy, two digits allowed';
    public $accuracy;

    public function __construct(int $accuracy)
    {
        parent::__construct(['accuracy' => $accuracy]);
    }

}
