<?php namespace App\Repository;

use Ewll\DBBundle\Repository\Repository;

class PayoutMethodRepository extends Repository
{
    public function findWithBalances()
    {
        $statement = $this->dbClient->prepare(<<<SQL
SELECT pm.id, pm.name, pm.balanceKey, pa.balance
FROM payoutMethod pm
LEFT JOIN paymentAccount pa ON pa.paymentSystemId = pm.paymentSystemId
WHERE
    pa.isDeleted = 0
    AND pa.isActive = 1
    AND FIND_IN_SET('withdraw', pa.enabled) > 0
SQL
        )->execute();

        $data = $statement->fetchArrays();
        $items = [];
        foreach ($data as $row) {
            if (!isset($items[$row['name']])) {
                $items[$row['name']] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'balanceKey' => $row['balanceKey'],
                    'balances' => [],
                ];
            }
            $items[$row['name']]['balances'][] = json_decode($row['balance']);
        }

        return array_values($items);
    }
}
