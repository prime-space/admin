<?php namespace App\Command;

use App\AccessRule\AccessChecker;
use App\AccessRule\AccessRuleInterface;
use App\AccessRule\AccessRuleListInterface;
use App\AccessRule\AccessRuleProvider;
use App\Entity\User;
use App\Exception\AccessRuleValidationException;
use App\Exception\CommandException;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class EditUserAccessRuleCommand extends AbstractCommand
{
    const COMMAND_NAME = 'user:access-rules';

    private $accessRuleProvider;
    private $accessChecker;

    public function __construct(
        Logger $logger,
        AccessRuleProvider $accessRuleProvider,
        AccessChecker $accessChecker
    ) {
        parent::__construct(self::COMMAND_NAME);
        $this->logger = $logger;
        $this->accessRuleProvider = $accessRuleProvider;
        $this->accessChecker = $accessChecker;
    }

    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->addArgument('login', InputArgument::REQUIRED);
        $this->addArgument('rule', InputArgument::OPTIONAL);
        $this->addArgument('id', InputArgument::OPTIONAL);

        $this->addOption('revoke', null, InputOption::VALUE_NONE);
        $this->addOption('grant', null, InputOption::VALUE_NONE);
    }

    protected function do(InputInterface $input, OutputInterface $output)
    {
        $style = new SymfonyStyle($input, $output);
        $login = $input->getArgument('login');
        $accessRuleName = $input->getArgument('rule');
        $id = $input->getArgument('id');

        $grant = $input->getOption('grant');
        $revoke = $input->getOption('revoke');
        try {
            if ($login === null) {
                throw new CommandException('Provide User login!');
            }
            /** @var User|null $user */
            $user = $this->repositoryProvider->get(User::class)->findOneBy(['login' => $login]);
            if ($user === null) {
                throw new CommandException('User not found!');
            }
            if ($grant !== false && $revoke !== false) {
                throw new CommandException('Only one option allowed!');
            }
            if ($accessRuleName !== null) {
                if ($grant === false && $revoke === false) {
                    throw new CommandException('Provide grant or revoke option!');
                }
                /** @var AccessRuleInterface $accessRule */
                $accessRule = $this->accessRuleProvider->getByName($accessRuleName);
                if ($accessRule === null) {
                    throw new CommandException('Access rule not found');
                }
                if ($grant !== false) {
                    if ($accessRule instanceof AccessRuleListInterface && $id === null) {
                        throw new CommandException('Provide id');
                    }
                    $this->grantAccess($user, $accessRule, $id);
                } else {
                    $this->revokeAccess($user, $accessRule, $id);
                }
                $this->repositoryProvider->get(User::class)->update($user, ['accessRights']);
            }
            $rows = $this->compileOutputTableRows($user);
            $style->table(['Id', 'Login', 'Rule Name', 'List'], $rows);
        } catch (AccessRuleValidationException $e) {
            $style->error('Invalid id');
        } catch (CommandException $e) {
            $style->error($e->getMessage());
        }
    }

    private function compileOutputTableRows(User $user): array
    {
        $rows = [];
        foreach ($user->accessRights as $accessRight) {
            $list = '';
            $accessRule = $this->accessRuleProvider->getById($accessRight['id']);
            if ($accessRule instanceof AccessRuleListInterface) {
                $list = implode(',', $accessRight['list']);
            }
            $rows[] = [$user->id, $user->login, $accessRule->getName(), $list];
        }

        return $rows;
    }

    private function revokeAccess(User $user, AccessRuleInterface $accessRule, int $id = null): void
    {
        if (!$this->accessChecker->isGranted($accessRule, $user, $id)) {
            return;
        }
        if ($accessRule instanceof AccessRuleListInterface) {
            $accessRightKey = array_search($accessRule->getId(), array_column($user->accessRights, 'id'));
            if ($accessRightKey !== false) {
                $list = $user->accessRights[$accessRightKey]['list'] ?? [];
                $listIdKey = array_search($id, $list);
                if ($listIdKey === false) {
                    return;
                }
                unset($list[$listIdKey]);
                $list = array_values($list);
                $user->accessRights[$accessRightKey]['list'] = $list;
                if (count($list) > 0) {
                    return;
                }
            }
        }
        $this->removeUserAccessRightIfExists($user, $accessRule->getId());
    }

    private function removeUserAccessRightIfExists(User $user, int $userAccessRightId): void
    {
        foreach ($user->accessRights as $userAccessRightKey => $userAccessRight) {
            if ($userAccessRight['id'] === $userAccessRightId) {
                unset($user->accessRights[$userAccessRightKey]);
            }
        }
    }

    /** @throws AccessRuleValidationException */
    private function grantAccess(User $user, AccessRuleInterface $accessRule, int $id = null): void
    {
        if ($this->accessChecker->isGranted($accessRule, $user, $id)) {
            return;
        }
        $userAccessRight = ['id' => $accessRule->getId()];
        if ($accessRule instanceof AccessRuleListInterface) {
            $userAccessRight['list'] = [];
            $accessRightKey = array_search($accessRule->getId(), array_column($user->accessRights, 'id'));
            if ($accessRightKey !== false) {
                $list = $user->accessRights[$accessRightKey]['list'] ?? [];
                if (!$accessRule->isIdValid($id)) {
                    throw new AccessRuleValidationException();
                }
                if (!in_array($id, $list, true)) {
                    $list[] = $id;
                    $userAccessRight['list'] = $list;
                    $user->accessRights[$accessRightKey] = $userAccessRight;

                    return;
                }
            } else {
                $userAccessRight['list'][] = $id;
            }
        }
        $user->accessRights[] = $userAccessRight;
    }
}
