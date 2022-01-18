<?php


namespace App\Security;

use App;
use Nette;
use App\Model\Repositories\UsersRepository;
use Nette\Security\AuthenticationException;
use Nette\Security\SimpleIdentity;

final class Authenticator implements Nette\Security\Authenticator
{
    /**
     * @var UsersRepository
     */
    private $repository;

    public function __construct(UsersRepository $r)
    {
        $this->repository = $r;
    }

    public function authenticate(string $username, string $password): SimpleIdentity
    {
        $user = $this->repository->findPlain()->where('[us.name] = %s', $username)->fetch();
        unset($user->password);
        return new SimpleIdentity($user->user_id, null, (array) $user);


//        $login = $credentials[static::USERNAME];
//        $password = $credentials[static::PASSWORD];
//
//        $users = $this->repository->findAll()->where('[email] = %s', $login)->fetch();
//
//        return new Nette\Security\Identity($users->user_id, NULL, [
//            'name'  => $users->name,
//            'email' => $users->email,
//            ]);
    }
}