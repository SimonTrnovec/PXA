<?php

namespace App\Presenters;

use App;
use App\Model\Repositories\StudentsRepository;
use Nette;
use Nette\Application\UI\Form;
use App\Model\Repositories\UsersRepository;
use Nette\Security\Authenticator;



class BackendAuthPresenter extends Nette\Application\UI\Presenter
{
    /**
     * @inject
     * @var UsersRepository
     */
    public $usersRepository;

    /**
     * @inject
     * @var App\Security\Authenticator
     */
    public $authenticator;

    protected function startup()
    {
        parent::startup();

        if($this->user->isLoggedIn()){
            $this->redirect('Homepage:default');
        }
    }

    public function createComponentLoginForm(): Form
    {
        $form = new Form;
        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Prosím vyplňte své uživatelské jméno.');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Prosím vyplňte své heslo.');

        $form->addSubmit('send', 'Přihlásit');

        $form->onSuccess[] = [$this, 'loginFormSucceeded'];
        return $form;
    }

    public function loginFormSucceeded(Form $form, $values): void
    {
        $values = $form->getValues();
        $this->getUser()->setAuthenticator($this->authenticator);
        try {
            $this->getUser()->login($values->username, $values->password);
            $this->flashMessage('boli ste úspešne prihlásený');
            $this->redirect('Homepage:');

        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError('Nesprávné přihlašovací jméno nebo heslo.');
        }

    }

    public function createComponentRegisterForm(): Form
    {
        $form = new Form;
        $form->addText('name', 'Uživatelské jméno:')
            ->setRequired('Prosím vyplňte své uživatelské jméno.');

        $form->addText('email', 'Uživatelský E-mail:')
            ->setRequired('Prosím vyplňte své uživatelské jméno.');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Prosím vyplňte své heslo.');

        $form->addSubmit('send', 'Přihlásit');

        $form->onSuccess[] = [$this, 'registerFormSucceeded'];
        return $form;
    }

    public function registerFormSucceeded(Form $form, $values): void
    {
        $values = $form->getValues();

        $password = $this->authenticator->getHash($values->password);

        $userData = [
            'name' => $values->name,
            'email' => $values->email,
            'password' => $password,
        ];

        $this->usersRepository->insert($userData);

        $this->flashMessage('Účet bol vytvorený');

        $this->redirect('login');

    }

    public function handleLogout()
    {
        $this->getUser()->logout(true);
        $this->redirect('BackendAuth:login');
    }
}