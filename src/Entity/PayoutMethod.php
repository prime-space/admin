<?php namespace App\Entity;

use Ewll\DBBundle\Annotation as Db;

class PayoutMethod
{
    /** @Db\TinyIntType */
    public $id;
    /** @Db\TinyIntType */
    public $paymentSystemId;
    /** @Db\VarcharType(length = 64) */
    public $name;
    /** @Db\VarcharType(length = 64) */
    public $balanceKey;
}
