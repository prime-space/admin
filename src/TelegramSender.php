<?php namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class TelegramSender
{
    private const TELEGRAM_API_URL = 'https://api.telegram.org';

    private $messageBroker;
    private $guzzleClient;
    private $botToken;

    public function __construct(
        MessageBroker $messageBroker,
        Client $guzzleClient,
        string $botToken
    ) {
        $this->messageBroker = $messageBroker;
        $this->guzzleClient = $guzzleClient;
        $this->botToken = $botToken;
    }

    public function send(string $message, $recipientTelegramId): void
    {
        $this->messageBroker->createMessage(MessageBroker::QUEUE_TELEGRAM_NOTIFICATIONS_NAME, [
            'text' => $message,
            'recipientTelegramId' => $recipientTelegramId,
        ]);
    }

    /** @throws RequestException */
    public function doRequest(string $message, int $recipientTelegramId)
    {
        $data = ['chat_id' => $recipientTelegramId, 'text' => $message];
        $query = http_build_query($data);
        $url = sprintf('%s/bot%s/sendMessage?%s', self::TELEGRAM_API_URL, $this->botToken, $query);

        $options = [
            'timeout' => 6,
            'connect_timeout' => 6,
        ];
        $this->guzzleClient->post($url, $options);
    }
}
