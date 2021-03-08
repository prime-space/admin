<?php namespace App;

use App\Application\MerchantApplication;
use App\Entity\PaymentAccount;
use App\Entity\Service;
use App\Entity\Ticket;
use App\Entity\TicketMessage;
use App\Entity\User;
use App\Exception\FormValidationException;
use App\Exception\ServiceClientException;
use App\Exception\ServiceClientRequestException;
use App\Repository\ServiceRepository;
use App\Repository\TicketMessageRepository;
use App\TagServiceProvider\TagServiceProvider;
use Ewll\DBBundle\Repository\RepositoryProvider;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\ParameterBag;
use Exception;
use Ewll\DBBundle\DB\Client as DbClient;
use DateTime;
use LogicException;
use BadFunctionCallException;

class ServiceClient
{
    private $guzzleClient;
    private $repositoryProvider;
    private $logger;
    private $defaultDbClient;
    private $domain;
    private $telegramSender;
    private $chatId;
    private $tagServiceProvider;
    private $applications;

    public function __construct(
        GuzzleClient $guzzleClient,
        RepositoryProvider $repositoryProvider,
        Logger $logger,
        DbClient $defaultDbClient,
        string $domain,
        TelegramSender $telegramSender,
        string $chatId,
        TagServiceProvider $tagServiceProvider,
        iterable $applications
    ) {
        $this->guzzleClient = $guzzleClient;
        $this->repositoryProvider = $repositoryProvider;
        $this->logger = $logger;
        $this->defaultDbClient = $defaultDbClient;
        $this->domain = $domain;
        $this->telegramSender = $telegramSender;
        $this->chatId = $chatId;
        $this->tagServiceProvider = $tagServiceProvider;
        $this->applications = $applications;
    }

    public function syncAccount(PaymentAccount $account): bool
    {
        /** @var ServiceRepository $serviceRepository */
        $serviceRepository = $this->repositoryProvider->get(Service::class);
        $services = $serviceRepository->findBy(['appType' => Service::APP_TYPE_MERCHANT]);
        /** @var Service $service */
        foreach ($services as $service) {
            $application = $this->tagServiceProvider->get($this->applications, $service->appType);
            if ($application instanceof MerchantApplication) {
                $route = 'syncAccount';
                $enabled = $account->serviceId === $service->id ? implode(',', $account->enabled) : '';
                $assignedIds = $account->serviceId === $service->id ? $account->assignedIds : [];
                try {
                    $formParams = [
                        'id' => $account->id,
                        'paymentSystemId' => $account->paymentSystemId,
                        'name' => $account->name,
                        'config' => json_encode($account->config),
                        'weight' => $account->weight,
                        'enabled' => $enabled,
                        'assignedIds' => json_encode($assignedIds),
                        'isWhite' => $account->isWhite,
                        'isActive' => $account->isActive,
                    ];
                    $this->doRequest('post', $service->domain, $route, $service->secret, $formParams);
                } catch (RequestException $e) {
                    $this->logger->error('Sync account error', [
                        'id' => $account->id,
                        'route' => $route,
                        'message' => $e->getMessage()
                    ]);

                    return false;
                }
            }
        }

        return true;
    }

    /** @param PaymentAccount[] $accounts */
    public function pushBalances(array $accounts): void
    {
        $balances = [];
        foreach ($accounts as $account) {
            $balances[] = $account->compileServiceApiBalanceView();
        }

        /** @var ServiceRepository $serviceRepository */
        $serviceRepository = $this->repositoryProvider->get(Service::class);
        $services = $serviceRepository->findBy(['appType' => Service::APP_TYPE_MERCHANT]);
        /** @var Service $service */
        foreach ($services as $service) {
            $application = $this->tagServiceProvider->get($this->applications, $service->appType);
            if ($application instanceof MerchantApplication) {
                $route = "pushAccountBalances";
                try {
                    $formParams = ['balances' => json_encode($balances)];
                    $this->doRequest('post', $service->domain, $route, $service->secret, $formParams);
                } catch (RequestException $e) {
                    $this->logger->critical('Push account balances error', [
                        'serviceId' => $service->id,
                        'route' => $route,
                        'message' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    public function getAccountStat(): array
    {
        bcscale(0);
        $serviceRepository = $this->repositoryProvider->get(Service::class);
        $services = $serviceRepository->findBy(['appType' => Service::APP_TYPE_MERCHANT]);

        $stat = [];
        /** @var Service $service */
        foreach ($services as $service) {
            $route = 'getAccountStat';
            try {
                $response = $this->doRequest('post', $service->domain, $route, $service->secret);
                $data = json_decode($response->getBody(), true);
                foreach ($data as $metricName => $metric) {
                    foreach ($metric as $item) {
                        foreach ($item as $name => $value) {
                            if ($name !== 'payment_account_id') {
                                $stat[$metricName][$item['payment_account_id']][$name] = bcadd(
                                    $stat[$metricName][$item['payment_account_id']][$name] ?? 0,
                                    $value
                                );
                            }
                        }
                    }
                }
            } catch (RequestException $e) {
                return [];
            }
        }

        return $stat;
    }

    public function getSecret(string $auth): string
    {
        $secret = str_replace('Bearer', '', $auth);
        $secretTrimmed = trim($secret);

        return $secretTrimmed;
    }

    /** @throws ServiceClientException */
    public function ticketMethod(ParameterBag $post, Service $service): array
    {
        $this->defaultDbClient->beginTransaction();
        try {
            $ticket = Ticket::create(
                $service->id,
                $service->responsibleUserId,
                $post->get('userId'),
                $post->get('id'),
                $post->get('subject')
            );
            $ticket->isReplied = true;
            $this->repositoryProvider->get(Ticket::class)->create($ticket);
            $ticketMessage = TicketMessage::create(
                $ticket->id,
                $post->get('message'),
                null
            );
            $this->repositoryProvider->get(TicketMessage::class)->create($ticketMessage);
            /** @var User $responsibleUser */
            $responsibleUser = $this->repositoryProvider->get(User::class)->findOneBy([
                'id' => $service->responsibleUserId
            ]);
            $recipientTelegramId = $responsibleUser->telegramId ?? $this->chatId;
            $url = "https://{$this->domain}/#/tickets/{$ticket->id}";
            $message = "New ticket {$url}";
            $this->telegramSender->send($message, $recipientTelegramId);
            $this->defaultDbClient->commit();
        } catch (Exception $e) {
            $this->defaultDbClient->rollback();
            throw new ServiceClientException(403);
        }

        return [];
    }

    public function toCheckingMethod(int $shopId, ParameterBag $post, Service $service): array
    {
        $responsibleUser = $this->repositoryProvider->get(User::class)->findOneBy([
            'id' => $service->responsibleUserId
        ]);
        $url = "https://{$this->domain}/#/service/{$service->id}/shop/{$shopId}";
        $message = "New shop for checking {$url}";
        $recipientTelegramId = $responsibleUser->telegramId ?? $this->chatId;
        $this->telegramSender->send($message, $recipientTelegramId);

        return [];
    }

    /** @throws ServiceClientRequestException */
    public function shopStatus(Service $service, int $shopId, int $status): void
    {
        $route = "changeShopStatus/{$shopId}";
        try {
            $formParams = ['status' => $status];
            $this->doRequest('post', $service->domain, $route, $service->secret, $formParams);
        } catch (RequestException $e) {
            $this->logger->error('Shop status changing error', [
                'route' => $route,
                'shopId' => $shopId,
                'message' => $e->getMessage()
            ]);

            throw new ServiceClientRequestException();
        }
    }

    /** @throws ServiceClientRequestException */
    public function userStatus(Service $service, int $userId, bool $isBlocked): void
    {
        $route = "changeUserStatus/{$userId}";
        try {
            $formParams = ['isBlocked' => $isBlocked];
            $formData = ['form' => $formParams];
            $this->doRequest('post', $service->domain, $route, $service->secret, $formData);
        } catch (RequestException $e) {
            $this->logger->error('User status changing error', [
                'route' => $route,
                'userId' => $userId,
                'message' => $e->getMessage()
            ]);

            throw new ServiceClientRequestException();
        }
    }

    public function paymentAccountBalancesMethod(ParameterBag $post, Service $service): array
    {
        $views = [];
        /** @var PaymentAccount[] $paymentAccounts */
        $paymentAccounts = $this->repositoryProvider->get(PaymentAccount::class)->findAll();
        foreach ($paymentAccounts as $paymentAccount) {
            $views[] = $paymentAccount->compileServiceApiBalanceView();
        }

        return $views;
    }

    /** @throws ServiceClientRequestException */
    public function shop(Service $service, int $shopId): array
    {
        $route = "shop/{$shopId}";
        try {
            $response = $this->doRequest('get', $service->domain, $route, $service->secret);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            $this->logger->error('Get shop error', [
                'serviceId' => $service->id,
                'shopId' => $shopId,
                'route' => $route,
                'message' => $e->getMessage()
            ]);

            throw new ServiceClientRequestException();
        }
    }

    /** @throws ServiceClientRequestException */
    public function shopStatistics(Service $service, int $shopId): array
    {
        $route = "shopStatistics/{$shopId}";
        try {
            $response = $this->doRequest('get', $service->domain, $route, $service->secret);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            $this->logger->error('Get shop statistics error', [
                'serviceId' => $service->id,
                'shopId' => $shopId,
                'route' => $route,
                'message' => $e->getMessage()
            ]);

            throw new ServiceClientRequestException();
        }
    }

    /** @throws ServiceClientException */
    public function messagesMethod(int $ticketId, ParameterBag $post, Service $service): array
    {
        $ticketRepository = $this->repositoryProvider->get(Ticket::class);
        /** @var Ticket $ticket */
        $ticket = $ticketRepository->findOneBy(['serviceTicketId' => $ticketId, 'serviceId' => $service->id,]);
        if ($ticket === null) {
            throw new ServiceClientException(404);
        }

        /** @var TicketMessageRepository $ticketMessagesRepository */
        $ticketMessagesRepository = $this->repositoryProvider->get(TicketMessage::class);
        $messages = $ticketMessagesRepository->getMessagesByTicketIdOrderedById($ticket->id);
        $views = [];
        $messagesAuthors = $this->repositoryProvider->get(User::class)->findByRelativeIndexed($messages);
        /** @var TicketMessage $message */
        foreach ($messages as $message) {
            /** @var User $user */
            $user = $messagesAuthors[$message->userId] ?? null;
            $views[] = $message->compileServiceApiView($user);
        }

        return $views;
    }

    /** @throws ServiceClientException */
    public function messageMethod(int $ticketId, ParameterBag $post, Service $service): array
    {
        /** @var Ticket $ticket */
        $ticket = $this->repositoryProvider->get(Ticket::class)->findOneBy([
            'serviceId' => $service->id,
            'serviceTicketId' => $ticketId,
        ]);
        if ($ticket === null) {
            throw new ServiceClientException(404);
        }

        $ticketMessage = TicketMessage::create(
            $ticket->id,
            $post->get('text')
        );
        $this->defaultDbClient->beginTransaction();
        try {
            $this->repositoryProvider->get(TicketMessage::class)->create($ticketMessage);
            $ticket->lastMessageTs = new DateTime();
            if ($ticket->isReplied === false) {
                $ticket->isReplied = true;
                /** @var User $responsibleUser */
                $responsibleUser = $this->repositoryProvider->get(User::class)->findOneBy([
                    'id' => $ticket->responsibleUserId
                ]);
                $url = "https://{$this->domain}/#/tickets/{$ticket->id}";
                $message = "New message {$url}";
                $recipientTelegramId = $responsibleUser->telegramId ?? $this->chatId;
                $this->telegramSender->send($message, $recipientTelegramId);
            }
            $this->repositoryProvider->get(Ticket::class)->update($ticket);
            $this->defaultDbClient->commit();
        } catch (Exception $e) {
            $this->defaultDbClient->rollback();
            throw new ServiceClientException(403);
        }

        return [];
    }

    /** @throws ServiceClientRequestException */
    public function sendMessage(int $serviceId, int $ticketId): void
    {
        /** @var Service $service */
        $service = $this->repositoryProvider->get(Service::class)->findOneBy(['id' => $serviceId]);
        if ($service === null) {
            throw new LogicException("Service with id $serviceId does not exist");
        }
        $route = 'newMessage';
        try {
            $formParams = ['ticketId' => $ticketId];
            $this->doRequest('post', $service->domain, $route, $service->secret, $formParams);
        } catch (RequestException $e) {
            $this->logger->error('Send message to service error', [
                'serviceId' => $serviceId,
                'ticketId' => $ticketId,
                'route' => $route,
                'message' => $e->getMessage()
            ]);

            throw new ServiceClientRequestException();
        }
    }

    /** @throws ServiceClientRequestException */
    public function find(Service $service, string $query)
    {
        $route = 'find';
        try {
            $formParams = ['query' => $query];
            $response = $this->doRequest('post', $service->domain, $route, $service->secret, $formParams);
            $data = json_decode($response->getBody(), true);

            return $data;
        } catch (RequestException $e) {
            $this->logger->error('Finder request error', [
                'route' => $route,
                'message' => $e->getMessage()
            ]);

            throw new ServiceClientRequestException();
        }
    }

    /** @throws ServiceClientRequestException */
    public function getStatistic(Service $service)
    {
        $route = 'statistic';
        try {
            $response = $this->doRequest('post', $service->domain, $route, $service->secret);
            $data = json_decode($response->getBody(), true);

            return $data;
        } catch (RequestException $e) {
            $this->logger->error('Request error', [
                'route' => $route,
                'message' => $e->getMessage()
            ]);

            throw new ServiceClientRequestException();
        }
    }

    /** @throws ServiceClientRequestException */
    public function getListingData(Service $service, string $listing, int $rowsPerPage, int $pageId, $params = [])
    {
        $route = 'listingData';
        try {
            $queryParams = [
                'listing' => $listing,
                'rowsPerPage' => $rowsPerPage,
                'pageId' => $pageId,
            ];
            $formData = ['form' => $params];
            $response = $this->doRequest('post', $service->domain, $route, $service->secret, $formData, $queryParams);
            $data = json_decode($response->getBody(), true);

            return $data;
        } catch (RequestException $e) {
            $this->logger->error('Request error', [
                'route' => $route,
                'message' => $e->getMessage()
            ]);

            throw new ServiceClientRequestException();
        }
    }

    /**
     * @throws ServiceClientRequestException
     * @throws FormValidationException
     */
    public function listingAdd(Service $service, string $listing, $params = [])
    {
        $route = 'listingAdd';
        try {
            $queryParams = [
                'listing' => $listing,
            ];
            $formData = ['form' => $params];
            $response = $this->doRequest('post', $service->domain, $route, $service->secret, $formData, $queryParams);
            $data = json_decode($response->getBody(), true);

            return $data;
        } catch (RequestException $e) {
            $response = json_decode($e->getResponse()->getBody(), true);
            if (isset($response['embeddedFormErrors'])) {
                throw new FormValidationException($response['embeddedFormErrors']);
            }
            $this->logger->error('Request error', [
                'route' => $route,
                'message' => $e->getMessage()
            ]);

            throw new ServiceClientRequestException();
        }
    }

    /** @throws ServiceClientRequestException */
    public function userData(Service $service, int $userId): array
    {
        $route = "user/{$userId}";
        try {
            $response = $this->doRequest('get', $service->domain, $route, $service->secret);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            $this->logger->error('Get user data error', [
                'serviceId' => $service->id,
                'userId' => $userId,
                'route' => $route,
                'message' => $e->getMessage()
            ]);

            throw new ServiceClientRequestException();
        }
    }

    /** @throws ServiceClientRequestException */
    public function shopPersonalPaymentMethodSettings(Service $service, array $methods, int $shopId): array
    {
        $route = "shopPersonalPaymentMethodSettings/{$shopId}";
        try {
            $response = $this->doRequest('post', $service->domain, $route, $service->secret, $methods);
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            $this->logger->error('Shop personal payment method settings error', [
                'serviceId' => $service->id,
                'route' => $route,
                'message' => $e->getMessage()
            ]);

            throw new ServiceClientRequestException();
        }
    }

    /** @throws ServiceClientRequestException */
    public function userPersonalPayoutMethodSettings(Service $service, array $methods, int $userId): array
    {
        $route = "userPersonalPayoutMethodSettings/{$userId}";
        try {
            $response = $this->doRequest('post', $service->domain, $route, $service->secret, $methods);
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            $this->logger->error('User personal payout method settings error', [
                'serviceId' => $service->id,
                'route' => $route,
                'message' => $e->getMessage()
            ]);

            throw new ServiceClientRequestException();
        }
    }

    /** @throws ServiceClientRequestException */
    public function payment(Service $service, int $paymentId): array
    {
        $route = "payment/{$paymentId}";
        try {
            $response = $this->doRequest('get', $service->domain, $route, $service->secret);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            $this->logger->error('Payment request error', [
                'route' => $route,
                'message' => $e->getMessage()
            ]);
        }

            throw new ServiceClientRequestException();
    }

    /** @throws ServiceClientRequestException */
    public function paymentRefund(Service $service, int $paymentId): array
    {
        $route = "paymentRefund/{$paymentId}";
        try {
            $response = $this->doRequest('post', $service->domain, $route, $service->secret);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            $this->logger->error('Payment request error', [
                'route' => $route,
                'message' => $e->getMessage()
            ]);
        }

            throw new ServiceClientRequestException();
    }

    /** @throws ServiceClientRequestException */
    public function paymentMethods(Service $service)
    {
        $route = 'paymentMethods';
        try {
            $response = $this->doRequest('get', $service->domain, $route, $service->secret);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            $this->logger->error('Get paymentMethods error', [
                'serviceId' => $service->id,
                'route' => $route,
                'message' => $e->getMessage()
            ]);

            throw new ServiceClientRequestException();
        }
    }

    /** @throws ServiceClientRequestException */
    public function paymentMethodSettings(Service $service, array $formData): void
    {
        $route = 'paymentMethodSettings';
        try {
            $this->doRequest('post', $service->domain, $route, $service->secret, $formData);
        } catch (RequestException $e) {
            $this->logger->error('Payment method settings error', [
                'serviceId' => $service->id,
                'route' => $route,
                'message' => $e->getMessage()
            ]);

            throw new ServiceClientRequestException();
        }
    }

    /** @throws ServiceClientRequestException */
    public function payoutMethods(Service $service)
    {
        $route = 'payoutMethods';
        try {
            $response = $this->doRequest('get', $service->domain, $route, $service->secret);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            $this->logger->error('Get paymentSystems error', [
                'serviceId' => $service->id,
                'route' => $route,
                'message' => $e->getMessage()
            ]);

            throw new ServiceClientRequestException();
        }
    }

    /** @throws ServiceClientRequestException */
    public function payoutMethodSettings(Service $service, array $formData): void
    {
        $route = 'payoutMethodSettings';
        try {
            $this->doRequest('post', $service->domain, $route, $service->secret, $formData);
        } catch (RequestException $e) {
            $this->logger->error('Payment system settings error', [
                'serviceId' => $service->id,
                'route' => $route,
                'message' => $e->getMessage()
            ]);

            throw new ServiceClientRequestException();
        }
    }

    /** @throws ServiceClientRequestException */
    public function queues(Service $service): array
    {
        $route = 'queues';
        try {
            $response = $this->doRequest('get', $service->domain, $route, $service->secret);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            $this->logger->error('Get queues error', [
                'serviceId' => $service->id,
                'route' => $route,
                'message' => $e->getMessage()
            ]);

            throw new ServiceClientRequestException();
        }
    }

    /** @throws ServiceClientRequestException */
    public function shopDailyLimit(Service $service, int $shopId, array $formData): void
    {
        $route = "shopDailyLimit/{$shopId}";
        try {
            $this->doRequest('post', $service->domain, $route, $service->secret, $formData);
        } catch (RequestException $e) {
            $this->logger->error('Shop daily limit changing error', [
                'route' => $route,
                'shopId' => $shopId,
                'message' => $e->getMessage()
            ]);

            throw new ServiceClientRequestException();
        }
    }

    /** @throws ServiceClientRequestException */
    public function getPayoutMethodsWaitingIndexedByMethod()
    {
        $route = "payoutMethodsWaiting";
        $serviceRepository = $this->repositoryProvider->get(Service::class);
        /** @var Service[] $services */
        $services = $serviceRepository->findBy(['appType' => Service::APP_TYPE_MERCHANT]);
        try {
            $waitings = [];
            foreach ($services as $service) {
                $response = $this->doRequest('post', $service->domain, $route, $service->secret);
                $data = json_decode($response->getBody(), true);
                foreach ($data as $paymentMethodKey => $amount) {
                    if (!isset($waitings[$paymentMethodKey])) {
                        $waitings[$paymentMethodKey] = '0';
                    }
                    $waitings[$paymentMethodKey] = bcadd($waitings[$paymentMethodKey], $amount, 2);
                }
            }

            return $waitings;
        } catch (RequestException $e) {
            $this->logger->error(__FUNCTION__.' error', [
                'route' => $route,
                'message' => $e->getMessage()
            ]);

            throw new ServiceClientRequestException();
        }
    }

    /** @throws ServiceClientException */
    public function deactivatePaymentAccountMethod(int $paymentAccountId, ParameterBag $post, Service $service): array
    {
        /** @var PaymentAccount|null $paymentAccount */
        $paymentAccount = $this->repositoryProvider->get(PaymentAccount::class)->findById($paymentAccountId);
        if ($paymentAccount === null) {
            throw new ServiceClientException(404);
        }
        $paymentAccount->isActive = false;
        $this->repositoryProvider->get(PaymentAccount::class)->update($paymentAccount, ['isActive']);

        return [];
    }

    /** @throws ServiceClientRequestException */
    public function changeEmail(Service $service, int $paymentId, array $formData): void
    {
        $route = "changeEmail/{$paymentId}";
        try {
            $this->doRequest('post', $service->domain, $route, $service->secret, $formData);
        } catch (RequestException $e) {
            $this->logger->error('Change email request error', [
                'route' => $route,
                'message' => $e->getMessage()
            ]);

            throw new ServiceClientRequestException();
        }
    }

    /** @throws ServiceClientRequestException */
    public function shopDomainSchemeChange(Service $service, int $shopId, array $formData): void
    {
        $route = "shopDomainSchemeChange/{$shopId}";
        try {
            $this->doRequest('post', $service->domain, $route, $service->secret, $formData);
        } catch (RequestException $e) {
            $this->logger->error('Shop domain and scheme changing error', [
                'route' => $route,
                'shopId' => $shopId,
                'message' => $e->getMessage()
            ]);

            throw new ServiceClientRequestException();
        }
    }

    /** @throws ServiceClientRequestException */
    public function paymentShots(Service $service, int $paymentId)
    {
        $route = "paymentShots/{$paymentId}";
        try {
            $response = $this->doRequest('get', $service->domain, $route, $service->secret);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            $this->logger->error('Get paymentShots error', [
                'serviceId' => $service->id,
                'route' => $route,
                'message' => $e->getMessage()
            ]);

            throw new ServiceClientRequestException();
        }
    }

    /** @throws ServiceClientRequestException */
    public function executePayment(Service $service, int $paymentId, array $formData): void
    {
        $route = "executePayment/{$paymentId}";
        try {
            $this->doRequest('post', $service->domain, $route, $service->secret, $formData);
        } catch (RequestException $e) {
            $this->logger->error('Execute payment error', [
                'route' => $route,
                'paymentId' => $paymentId,
                'message' => $e->getMessage(),
            ]);

            throw new ServiceClientRequestException();
        }
    }

    private function doRequest(
        string $method,
        string $domain,
        string $route,
        string $secret,
        array $formParams = [],
        $queryParams = []
    ) {
        $query = count($queryParams) > 0 ? '?'.http_build_query($queryParams) : '';
        $url = "https://{$domain}/sf/adminApi/{$route}{$query}";
        $function = [$this->guzzleClient, $method];
        if (!is_callable($function)) {
            throw new BadFunctionCallException('Function ' . $function . ' is not callable');
        }
        $params = [
            'timeout' => 5,
            'connect_timeout' => 5,
            'headers' => [
                'Authorization' => "Bearer $secret",
            ],
            'form_params' => $formParams,
        ];
        $result = call_user_func($function, $url, $params);

        return $result;
    }
}
