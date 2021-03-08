<?php namespace App\Repository;

use Ewll\DBBundle\Repository\Repository;

class UserRepository extends Repository
{
    public function clearSessions()
    {
        $this->dbClient->prepare(<<<SQL
DELETE FROM userSession
WHERE lastActionTs < ADDDATE(NOW(), INTERVAL -2 DAY)
SQL
        )->execute();
    }
}
