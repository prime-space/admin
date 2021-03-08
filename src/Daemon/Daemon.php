<?php namespace App\Daemon;

use Ewll\DBBundle\Repository\RepositoryProvider;
use Exception;
use App\Logger\LogExtraDataKeeper;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\FlockStore;
use Wrep\Daemonizable\Command\EndlessCommand;

abstract class Daemon extends EndlessCommand
{
    /** @var Logger */
    protected $logger;
    /** @var RepositoryProvider */
    protected $repositoryProvider;
    /** @var LogExtraDataKeeper */
    protected $logExtraDataKeeper;

    public function setLogExtraDataKeeper(LogExtraDataKeeper $logExtraDataKeeper): void
    {
        $this->logExtraDataKeeper = $logExtraDataKeeper;
    }

    public function setRepositoryProvider(RepositoryProvider $repositoryProvider)
    {
        $this->repositoryProvider = $repositoryProvider;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logExtraDataKeeper->setData([
            'module' => 'daemon',
            'name' => $this->getName(),
        ]);
        $this->logger->info("Start {$this->getName()}");

        $store = new FlockStore();
        $factory = new Factory($store);
        $lock = $factory->createLock(preg_replace('/[^\w\d\s]/', '_', $this->getName()));

        if (!$lock->acquire()) {
            $this->logger->critical('Sorry, cannot lock file');

            return;
        }

        $io = new SymfonyStyle($input, $output);

        try {
            while (1) {
                $this->logExtraDataKeeper->setParam('iterationKey', microtime(true));
                $this->do($io);
                $this->repositoryProvider->clear();
            }
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
            // @TODO CHOICE: CLOSE CONNECTIONS OR SHUTDOWN
            $this->logger->critical('Shutdown daemon');
            $this->shutdown();
        }

        $this->logger->debug("Done {$this->getName()}");
    }

    /** @throws Exception */
    abstract protected function do(SymfonyStyle $io);
}
