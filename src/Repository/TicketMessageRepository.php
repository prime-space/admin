<?php namespace App\Repository;

use Ewll\DBBundle\Repository\Repository;

class TicketMessageRepository extends Repository
{
    public function getMessagesByTicketIdOrderedById(int $ticketId)
    {
        $prefix = 't1';
        $statement = $this->dbClient->prepare(<<<SQL
SELECT {$this->getSelectList($prefix)}
FROM ticketMessage $prefix
WHERE
    $prefix.ticketId = :ticketId
ORDER BY
    $prefix.id ASC
SQL
        )->execute([
            'ticketId' => $ticketId,
        ]);

        $messages = $this->hydrator->hydrateMany(
            $this->config,
            $prefix,
            $statement,
            $this->getFieldTransformationOptions()
        );

        return $messages;
    }
}
