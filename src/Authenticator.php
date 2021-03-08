<?php namespace App;

use App\AccessRule\AccessChecker;
use App\AccessRule\AccessRuleInterface;
use App\Entity\User;
use App\Entity\UserSession;
use Ewll\DBBundle\Repository\RepositoryProvider;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

class Authenticator
{
    private $repositoryProvider;
    private $user;
    private $accessChecker;

    public function __construct(RepositoryProvider $repositoryProvider, AccessChecker $accessChecker)
    {
        $this->repositoryProvider = $repositoryProvider;
        $this->accessChecker = $accessChecker;
    }

    public function signIn($login, $password)
    {
        $hash = hash('sha256', $password);

        $user = $this->repositoryProvider->get(User::class)->findOneBy(['login' => $login, 'pass' => $hash]);

        if (null === $user) {
            return false;
        }

        $time = time();
        $crypt = hash('sha256', $time.$login);
        $token = hash('sha256', $login.$time);

        //@TODO unique crypt
        $userSession = UserSession::create($user->id, $crypt, $token);
        $this->repositoryProvider->get(UserSession::class)->create($userSession);

        $this->setSessionCookie($crypt, 86400*10);

        return true;
    }

    public function isSigned(Request $request)
    {
        $crypt = $request->cookies->get('s');

        if (null === $crypt) {
            return false;
        }

        //@TODO move to daemon
        $this->repositoryProvider->get(User::class)->clearSessions();
        /** @var UserSession|null $userSession */
        $userSession = $this->repositoryProvider->get(UserSession::class)->findOneBy(['crypt' => $crypt]);
        $user = $userSession !== null
            ? $this->repositoryProvider->get(User::class)->findById($userSession->userId)
            : null;
        if ($user !== null) {
            $user->token = $userSession->token;
            $this->user = $user;
        }

        return null !== $this->user;
    }

    public function signOut()
    {
        //@TODO drop db session
        $this->setSessionCookie('', -3600);
    }

    public function getUser(): ?User
    {
        if (null === $this->user) {
            throw new RuntimeException('No user');
        }

        return $this->user;
    }

    private function setSessionCookie($value, $duration)
    {
        SetCookie('s', $value, time()+$duration, '/', $_SERVER['HTTP_HOST'], true, true);
    }

    public function isGranted(AccessRuleInterface $accessRule, int $id = null): bool
    {
        $isGranted = $this->accessChecker->isGranted($accessRule, $this->getUser(), $id);

        return $isGranted;
    }
}
