<?php namespace App\Repository;

use Ewll\DBBundle\Repository\Repository;

class TicketRepository extends Repository
{
    public function getTicketsOrderedByUnreadAndDateWithPagination(int $limit, int $pageId)
    {
        $prefix = 't1';
        $offset = ($pageId - 1) * $limit;
        $statement = $this->dbClient->prepare(<<<SQL
SELECT SQL_CALC_FOUND_ROWS {$this->getSelectList($prefix)}
FROM ticket $prefix
ORDER BY
    $prefix.isReplied DESC, $prefix.lastMessageTs DESC
LIMIT $offset, $limit
SQL
        )->execute();

        $tickets = $this->hydrator->hydrateMany(
            $this->config,
            $prefix,
            $statement,
            $this->getFieldTransformationOptions()
        );

        return $tickets;
    }
}
