<?php namespace App\Daemon;

use App\MessageBroker;
use App\TelegramSender;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TelegramNotificationDaemon extends Daemon
{
    private $messageBroker;
    private $chatId;
    private $botToken;
    private $telegramSender;

    public function __construct(
        LoggerInterface $logger,
        MessageBroker $messageBroker,
        string $chatId,
        string $botToken,
        TelegramSender $telegramSender
    ) {
        parent::__construct();
        $this->logger = $logger;
        $this->messageBroker = $messageBroker;
        $this->chatId = $chatId;
        $this->botToken = $botToken;
        $this->telegramSender = $telegramSender;
    }

    /** @throws RequestException */
    protected function do(SymfonyStyle $io)
    {
        $message = $this->messageBroker->getMessage(MessageBroker::QUEUE_TELEGRAM_NOTIFICATIONS_NAME);
        $this->logger->info(
            "Send notification. Text - {$message['text']}. RecipientTelegramId - {$message['recipientTelegramId']}"
        );
        $this->telegramSender->doRequest($message['text'], $message['recipientTelegramId']);
        $this->logger->info('Success');
    }
}
