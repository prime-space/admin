<?php namespace App\Entity;

use Ewll\DBBundle\Annotation as Db;

class UserLog
{
    const METHOD_EXECUTE_PAYMENT = 'payment_execute';
    const METHOD_REFUND_PAYMENT = 'payment_refund';

    /** @Db\IntType */
    public $id;
    /** @Db\IntType */
    public $userId;
    /** @Db\VarcharType(length = 64) */
    public $method;
    /** @Db\BigIntType */
    public $methodId;
    /** @Db\TimestampType */
    public $createdTs;

    public static function create($userId, $method, $methodId): self
    {
        $item = new self();
        $item->userId = $userId;
        $item->method = $method;
        $item->methodId = $methodId;

        return $item;
    }
}
