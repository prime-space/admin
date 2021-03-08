<?php namespace App\Entity;

use Ewll\DBBundle\Annotation as Db;

class PaymentAccount
{
    /** @Db\IntType */
    public $id;
    /** @Db\TinyIntType */
    public $paymentSystemId;
    /** @Db\TinyIntType */
    public $serviceId;
    /** @Db\VarcharType(length = 64) */
    public $name;
    /** @Db\CipheredType */
    public $config;
    /** @Db\TinyIntType */
    public $weight;
    /** @Db\SetType("shop,merchant,withdraw") */
    public $enabled;
    /** @Db\JsonType */
    public $assignedIds;
    /** @Db\BoolType */
    public $isWhite;
    /** @Db\JsonType */
    public $balance = [];
    /** @Db\BoolType */
    public $isDeleted = false;
    /** @Db\BoolType */
    public $isActive = true;


    public $paymentSystemName;
    public $serviceDomain;


    public static function create(
        $paymentSystemId,
        $serviceId,
        $name,
        $config,
        $weight,
        $enabled,
        $assignedIds,
        $isWhite,
        $isActive
    ): self {
        $item = new self();
        $item->paymentSystemId = $paymentSystemId;
        $item->serviceId = $serviceId;
        $item->name = $name;
        $item->config = $config;
        $item->weight = $weight;
        $item->enabled = $enabled;
        $item->assignedIds = $assignedIds;
        $item->isWhite = $isWhite;
        $item->isActive = $isActive;

        return $item;
    }

    public function compileServiceApiBalanceView()
    {
        $view = [
            'id' => $this->id,
            'balance' => $this->balance,
        ];

        return $view;
    }

    public function compileBalanceVueView()
    {
        switch (count($this->balance) <=> 1) {
            case -1:
                $view = '';
                break;
            case 0:
                $view = reset($this->balance);
                break;
            case 1:
                $balances = [];
                foreach ($this->balance as $account => $amount) {
                    $balances[] = "$amount$account";
                }
                $view = implode(' ', $balances);
        }

        return $view;
    }

    public function compileBalanceLogView()
    {
        return $this->compileBalanceVueView();
    }
}
