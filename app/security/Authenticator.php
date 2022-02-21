<?php


namespace App\Security;

use App;
use Nette;
use App\Model\Repositories\UsersRepository;
use Nette\Security\AuthenticationException;
use Nette\Security\Identity;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;

final class Authenticator implements Nette\Security\Authenticator
{
    /**
     * @var UsersRepository
     */
    private $repository;


    private $passwords;

    public function __construct(UsersRepository $r, Nette\Security\Passwords $passwords)
    {
        $this->repository = $r;
        $this->passwords = $passwords;
    }

    public function authenticate(string $username, string $password): SimpleIdentity
    {
        $user = $this->repository->findPlain()->where('[us.name] = %s', $username)->fetch();

        if (!$user) {
            throw new Nette\Security\AuthenticationException('User not found.');
        }


        if (!$this->passwords->verify($password, $user->password)) {
            throw new Nette\Security\AuthenticationException('Invalid password.');
        }

        return new SimpleIdentity($user->user_id, NULL, [
            'name'  => $user->name,
        ]);


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

    public function getHash(string $password)
    {
        $hash = $this->passwords->hash($password);

        return $hash;
    }
}