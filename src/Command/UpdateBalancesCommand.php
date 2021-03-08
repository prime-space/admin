<?php namespace App\Command;

use App\Entity\PaymentAccount;
use App\Entity\PaymentSystem;
use App\Exception\CannotGetBallanceException;
use App\PaymentProvider\PaymentProviderInterface;
use App\Repository\PaymentAccountRepository;
use App\ServiceClient;
use App\TagServiceProvider\TagServiceProvider;
use Exception;
use Ewll\DBBundle\DB\Client as DbClient;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateBalancesCommand extends AbstractCommand
{
    const COMMAND_NAME = 'payment-account:update-balances';

    private $tagServiceProvider;
    private $paymentProviders;
    private $defaultDbClient;
    private $serviceClient;

    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
    }

    public function __construct(
        Logger $logger,
        TagServiceProvider $tagServiceProvider,
        iterable $paymentProviders,
        DbClient $defaultDbClient,
        ServiceClient $serviceClient
    ) {
        parent::__construct(self::COMMAND_NAME);
        $this->logger = $logger;
        $this->tagServiceProvider = $tagServiceProvider;
        $this->paymentProviders = $paymentProviders;
        $this->defaultDbClient = $defaultDbClient;
        $this->serviceClient = $serviceClient;
    }

    protected function do(InputInterface $input, OutputInterface $output)
    {
        /** @var PaymentAccountRepository $paymentAccountRepository */
        $paymentAccountRepository = $this->repositoryProvider->get(PaymentAccount::class);
        /** @var PaymentAccount[] $paymentAccounts */
        $paymentAccounts = $paymentAccountRepository->findBy(['isDeleted' => 0, 'isActive' => 1]);
        /** @var PaymentSystem[] $paymentSystems */
        $paymentSystems = $this->repositoryProvider->get(PaymentSystem::class)->findAll('id');

        $updatedPaymentAccounts = [];
        foreach ($paymentAccounts as &$paymentAccount) {
            $this->logExtraDataKeeper->setParam('paymentAccountId', $paymentAccount->id);
            $this->logger->info('Handling');
            $paymentSystem = $paymentSystems[$paymentAccount->paymentSystemId];
            /** @var PaymentProviderInterface $paymentProvider */
            $paymentProvider = $this->tagServiceProvider->get(
                $this->paymentProviders,
                $paymentSystem->name
            );

//            for ($i = 0; $i < 3; $i++) {
                try {
                    $balance = $paymentProvider->getBalance($paymentAccount->config);
                    $paymentAccount->balance = $balance;
                    $this->logger->info("Success! Balance: {$paymentAccount->compileBalanceLogView()}");
                    $updatedPaymentAccounts[] = $paymentAccount;

//                    break;
                } catch (CannotGetBallanceException $e) {
                    $this->logger->error('Error', ['message' => $e->getMessage()]);
//                    if ($i < 2) {
//                        $this->logger->info('Sleep before retry');
//                        sleep(3);
//                    }
                }
//            }
        }

        $this->defaultDbClient->beginTransaction();
        try {
            $paymentAccountRepository->resetAllBalances();
            foreach ($updatedPaymentAccounts as $paymentAccount) {
                $paymentAccountRepository->update($paymentAccount, ['balance']);
            }
            $this->defaultDbClient->commit();
            $this->logger->info('Push balances to services');
            $this->serviceClient->pushBalances($updatedPaymentAccounts);
        } catch (Exception $e) {
            $this->defaultDbClient->rollback();
            $this->logger->critical('Cannot update balances', ['error' => $e->getMessage()]);
        }
    }
}
