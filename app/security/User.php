<?php

namespace App\Security;

use App;
use App\Model\Enums\VisibilityStatesEnum;
use App\Model\Repositories\AdminSectionsRepository;
use App\Model\Repositories\AdminsRepository;
use App\Model\Repositories\CustomersRepository;
use App\Model\Repositories\SectionsRepository;
use Nette;
use Nette\Security\IAuthenticator;
use Nette\Security\IAuthorizator;
use Nette\Security\IUserStorage;
use Tracy\Debugger;

class User extends Nette\Security\User
{

    const NAMESPACE_BACKEND = 'BE';

    const NAMESPACE_BACKEND_PREVIOUS = 'PE';

    const NAMESPACE_FRONTEND = 'FE';

    /**
     * @var AdminSectionsRepository
     */
    private $adminSectionsRepository;

    /**
     * @var AdminsRepository
     */
    private $adminsRepository;

    /**
     * @var AdminsRepository
     */
    private $sectionsRepository;

    /**
     * @var \DibiRow|NULL
     */
    private $adminData;

    /**
     * @var array of \DibiRow
     */
    private $adminSections;

    /**
     * @var array of \DibiRow
     */
    private $sections;

    public function __construct(IUserStorage $storage, SectionsRepository $sr, AdminsRepository $ar, AdminSectionsRepository $asr, IAuthenticator $authenticator = NULL, IAuthorizator $authorizator = NULL)
    {
        parent::__construct($storage, $authenticator, $authorizator);

        $this->sectionsRepository = $sr;
        $this->adminSectionsRepository = $asr;
        $this->adminsRepository = $ar;
    }

    private function getAdminData()
    {
        if (!$this->adminData) {
            $this->adminData = $this->adminsRepository->find($this->getId());
        }

        return $this->adminData;
    }

    private function getAdminSections()
    {
        if (!$this->adminSections) {
            $this->adminSections = $this->adminSectionsRepository->getSections($this->getId());
        }

        return $this->adminSections;
    }

    private function getSections()
    {
        if (!$this->sections) {
            $this->sections = $this->sectionsRepository->findAll()->fetchAssoc('key');
        }

        return $this->sections;
    }

    public function hasAccess($sectionKey)
    {
        $admin = $this->getAdminData();

        $sections = $this->getSections();

        if (!isset($sections[$sectionKey])) {
            Debugger::log("Section '$sectionKey' does not exist.", Debugger::ERROR);

            return FALSE;
        } else {
            $section = $sections[$sectionKey];
        }

        if ($section->state != VisibilityStatesEnum::ENABLED) {
            Debugger::log("Section '{$sectionKey}' is not enabled.", Debugger::ERROR);

            return FALSE;
        }

        if (!$admin->has_restricted_access) {
            return TRUE;
        }

        $adminSections = $this->getAdminSections();

        return isset($adminSections[$sectionKey]);
    }

    public function refreshIdentity()
    {
        $namespace = $this->getStorage()->getNamespace();
        $id = $this->getId();

        if ($namespace == static::NAMESPACE_BACKEND) {
            $admin = $this->adminsRepository->find($id);

            if (!$admin || $admin->state != VisibilityStatesEnum::ENABLED) {
                return FALSE;
            }

            $this->getStorage()->setIdentity(new Nette\Security\Identity($admin->admin_id, NULL, [
                'medium_id' => $admin->medium_id,
                'name'      => $admin->name,
                'email'     => $admin->email,
            ]));

            return TRUE;
        } elseif ($namespace == static::NAMESPACE_FRONTEND) {
            $customer = $this->customersRepository->find($id);

            if (!$customer || $customer->state != App\Model\Enums\VisibilityStatesEnum::ENABLED) {
                return FALSE;
            }

            $this->getStorage()->setIdentity(new Nette\Security\Identity($customer->customer_id, NULL, (array) $customer));

            return TRUE;
        } else {
            throw new \LogicException("Namespace '$namespace' is not supported.'");
        }
    }

    public function loginAdmin($id)
    {
        $oldNamespace = $this->getStorage()->getNamespace();

        $admin = $this->adminsRepository->find($id);

        if (!$admin || $admin->state != VisibilityStatesEnum::ENABLED) {
            return FALSE;
        }

        // 1. Get current backend identity; may be needed
        $this->getStorage()->setNamespace(static::NAMESPACE_BACKEND);
        $currentIdentity = $this->getStorage()->getIdentity();

        // 2. Set current identity as previous if empty
        $this->getStorage()->setNamespace(static::NAMESPACE_BACKEND_PREVIOUS);
        if ($this->getStorage()->getIdentity() === NULL) {
            $this->login($currentIdentity);
        }

        // 3. Return back to backend identity and set new one
        $this->getStorage()->setNamespace(static::NAMESPACE_BACKEND);
        $this->login(new Nette\Security\Identity($admin->admin_id, NULL, [
            'medium_id' => $admin->medium_id,
            'name'      => $admin->name,
            'email'     => $admin->email,
        ]));

        $this->getStorage()->setNamespace($oldNamespace);

        return TRUE;
    }

    public function getPreviousAdminIdentity()
    {
        $this->getStorage()->setNamespace(static::NAMESPACE_BACKEND_PREVIOUS);

        $identity = $this->getStorage()->getIdentity();

        $this->getStorage()->setNamespace(static::NAMESPACE_BACKEND);

        return $identity;
    }

    public function restoreAdmin()
    {
        $oldNamespace = $this->getStorage()->getNamespace();

        $this->getStorage()->setNamespace(static::NAMESPACE_BACKEND_PREVIOUS);
        $previousIdentity = $this->getStorage()->getIdentity();

        if (!$previousIdentity) {
            return FALSE;
        }

        $this->logout(TRUE);

        $this->getStorage()->setNamespace(static::NAMESPACE_BACKEND);
        $this->login($previousIdentity);

        $this->getStorage()->setNamespace($oldNamespace);

        return TRUE;
    }

}