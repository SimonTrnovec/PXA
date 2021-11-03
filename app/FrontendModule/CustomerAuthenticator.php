<?php

namespace App\FrontendModule;

use App;
use App\Model\Enums\VisibilityStatesEnum;
use App\Model\Repositories\CustomersRepository;
use Nette;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\IIdentity;

class CustomerAuthenticator implements IAuthenticator
{
    use Nette\SmartObject;

    /**
     * @var \App\Model\Repositories\CustomersRepository
     */
    private $repository;

    /**
     * @var int
     */
    public $websiteId;

    public function __construct(CustomersRepository $r)
    {
        $this->repository = $r;
    }

    /**
     * Performs an authentication against e.g. database.
     * and returns IIdentity on success or throws AuthenticationException
     *
     * @param array $credentials
     *
     * @throws \Nette\Security\AuthenticationException
     * @return IIdentity
     */
    function authenticate(array $credentials)
    {
        $login = $credentials[static::USERNAME];
        $password = $credentials[static::PASSWORD];

        $customer = $this->repository->findAll()
            ->where('[login_email] = %s', $login)
            ->where('[created_website_id] = %i', $this->websiteId)
            ->where('[state] != %s', VisibilityStatesEnum::DELETED)
            ->fetch();

        if (!$customer) {
            throw new AuthenticationException('Používateľ so zadaným e-mailom neexistuje.', IAuthenticator::IDENTITY_NOT_FOUND);
        }

        if ($customer->state != VisibilityStatesEnum::ENABLED) {
            throw new AuthenticationException('Konto je momentálne zablokované.', IAuthenticator::NOT_APPROVED);
        }

        if (!password_verify($password, $customer->password)) {
            throw new AuthenticationException('Vami zadané heslo nie je správne!', self::INVALID_CREDENTIAL);
        }

        if ($customer->forgotten_password_hash !== NULL) {
            $this->repository->update($customer->customer_id, ['forgotten_password_hash' => NULL]);
        }

        return new Nette\Security\Identity($customer->customer_id, NULL, (array) $customer);
    }
}