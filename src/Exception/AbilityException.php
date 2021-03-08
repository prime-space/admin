<?php namespace App\Exception;

use Exception;

class AbilityException extends Exception
{
    const CODE_BAD_REQUEST = 1;
    const CODE_REQUEST_EXCEPTION = 2;

    private $data;

    public function __construct(array $data = [], int $code = self::CODE_BAD_REQUEST)
    {
        $this->data = $data;
        parent::__construct('', $code);
    }

    public function getData(): array
    {
        return $this->data;
    }
}
