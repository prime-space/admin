<?php namespace App\Repository;

use Ewll\DBBundle\Repository\Repository;

class PaymentAccountRepository extends Repository
{
    public function resetAllBalances()
    {
        $this->dbClient->prepare(<<<SQL
UPDATE paymentAccount
SET balance = '[]'
SQL
        )->execute();
    }
}
