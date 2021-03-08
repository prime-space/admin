<?php namespace App\Entity;

use Ewll\DBBundle\Annotation as Db;

class PaymentSystem
{
    /** @Db\TinyIntType */
    public $id;
    /** @Db\VarcharType(length = 64) */
    public $name;
    /** @Db\JsonType */
    public $config;


    public static function create($name, $config): self
    {
        $item = new self();
        $item->name = $name;
        $item->config = $config;

        return $item;
    }

    public function compileVueSelectView(): array
    {
        $view = [
            'value' => $this->id,
            'text' => $this->name,
        ];

        return $view;
    }
}
