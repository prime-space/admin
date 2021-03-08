<?php namespace App;

use Ewll\DBBundle\DB\Client;

class MessageBroker
{
    const QUEUE_TELEGRAM_NOTIFICATIONS_NAME = 'telegram';

    const QUEUE_NAMES = [
        self::QUEUE_TELEGRAM_NOTIFICATIONS_NAME,
    ];

    private $queueDbClient;

    public function __construct(Client $queueDbClient)
    {
        $this->queueDbClient = $queueDbClient;
    }

    public function getMessage(string $queue): array
    {
        while (1) {
            $statement = $this->queueDbClient
                ->prepare("CALL sp_get_message('$queue')")
                ->execute();

            $data = $statement->fetchColumn();
            if (null === $data) {
                continue;
            }
            $data = json_decode($data, true);

            break;
        }

        return $data;
    }

    public function createMessage(string $queue, array $data, int $delay = 0): void
    {
        $this->queueDbClient
            ->prepare("CALL sp_create_message('$queue', :message, :delay)")
            ->execute([
                'message' => json_encode($data),
                'delay' => $delay,
            ]);
    }

    public function optimizeQueueTable(string $queueName): void
    {
        $this->queueDbClient
            ->prepare("CALL sp_optimize_queue_table(:queueName)")
            ->execute([
                'queueName' => $queueName,
            ]);
    }
}
