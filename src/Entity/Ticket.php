<?php namespace App\Entity;

use App\VueViewCompiler;
use Ewll\DBBundle\Annotation as Db;

class Ticket
{
    /** @Db\IntType */
    public $id;
    /** @Db\IntType */
    public $responsibleUserId;
    /** @Db\IntType */
    public $serviceId;
    /** @Db\IntType */
    public $serviceUserId;
    /** @Db\IntType */
    public $serviceTicketId;
    /** @Db\VarcharType(length = 256) */
    public $subject;
    /** @Db\BoolType */
    public $isReplied = false;
    /** @Db\TimestampType */
    public $lastMessageTs;
    /** @Db\TimestampType */
    public $createdTs;

    public static function create($serviceId, $responsibleUserId, $serviceUserId, $serviceTicketId, $subject): self
    {
        $item = new self();
        $item->serviceId = $serviceId;
        $item->responsibleUserId = $responsibleUserId;
        $item->serviceUserId = $serviceUserId;
        $item->serviceTicketId = $serviceTicketId;
        $item->subject = $subject;

        return $item;
    }

    public function compileView(string $serviceName, string $responsibleUser): array
    {
        $view = [
            'id' => $this->id,
            'subject' => $this->subject,
            'serviceName' => $serviceName,
            'responsible' => $responsibleUser,
            'lastMessageTs' => $this->lastMessageTs->format(VueViewCompiler::MOMENT_DATE_FORMAT),
            'isReplied' => $this->isReplied,
        ];

        return $view;
    }
}
