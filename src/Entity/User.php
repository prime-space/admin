<?php namespace App\Entity;

use Ewll\DBBundle\Annotation as Db;

class User
{
    /** @Db\IntType */
    public $id;
    /** @Db\VarcharType(length = 16) */
    public $login;
    /** @Db\VarcharType(length = 64) */
    public $pass;
    /** @Db\VarcharType(length = 64) */
    public $name;
    /** @Db\VarcharType(length = 64) */
    public $lastName;
    /** @Db\IntType */
    public $telegramId;
    /** @Db\JsonType */
    public $accessRights = [];
    public $token;

    public static function create($login, $pass): self
    {
        $item = new self();
        $item->login = $login;
        $item->pass = $pass;

        return $item;
    }

    public function compileFullName(): string
    {
        $fullName = $this->name . ' ' . $this->lastName;

        return $fullName;
    }

    public function compileJsConfigView(): array
    {
        $view = [
            'id' => $this->id,
            'fullName' => $this->compileFullName()
        ];

        return $view;
    }
}
