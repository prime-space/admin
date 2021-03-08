<?php namespace App\Entity;

use App\VueViewCompiler;
use Ewll\DBBundle\Annotation as Db;

class TicketMessage
{
    /** @Db\IntType */
    public $id;
    /** @Db\IntType */
    public $ticketId;
    /** @Db\TextType */
    public $text;
    /** @Db\IntType */
    public $userId;
    /** @Db\TimestampType */
    public $createdTs;

    public static function create($ticketId, $text, $userId = null): self
    {
        $item = new self();
        $item->ticketId = $ticketId;
        $item->text = $text;
        $item->userId = $userId;

        return $item;
    }

    public function compileVueView(Ticket $ticket, User $user = null): array
    {
        $view = [
            'id' => $this->id,
            'text' => $this->text,
            'author' => $user === null ? 'Client' : $user->compileFullName(),
            'serviceUserId' => $ticket->serviceUserId,
            'serviceId' => $ticket->serviceId,
            'createdTs' => $this->createdTs->format('m-d H:i'),
            'isAnswer' => $user !== null,
        ];

        return $view;
    }

    public function compileServiceApiView(User $user = null): array
    {
        $view = [
            'id' => $this->id,
            'text' => $this->text,
            'answerUserName' => $user === null ? null : $user->compileFullName(),
            'createdTs' => $this->createdTs->format('m-d H:i')
        ];

        return $view;
    }
}
