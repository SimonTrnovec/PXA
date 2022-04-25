<?php

namespace App\Presenters;
use App\Model\Repositories\UsersRepository;
use App\Model\Repositories\RoleStatesEnum;

use Nette;
use Nette\Application\UI\Form;


final class AdminsPresenter extends BasePresenter
{

    /**
     * @inject
     * @var UsersRepository
     */
    public $userRepository;

    public function startup()
    {
        parent::startup();

        if($this->isAdmin()){
            $this->redirect('BackendAuth:login');
        }
    }

    public function actionDefault(){
        parent::startup();
        $this->template->user = $this->getUser();
        $this->template->admins = $this->userRepository->findAll()->fetchAll();
        $this->template->role = RoleStatesEnum::getItems();



    }

    protected function createComponentAdminForm(): Form
    {
        $form = new Form;
        $form->addText('name', 'Meno');

        $form->addEmail('email', 'E-mail');

        $form->addSelect('admin', 'rola',  RoleStatesEnum::getItems());

        return $form;
    }

    private function getAdmin($id)
    {
        $admin = $this->userRepository->findAll()->where('[us.user_id] = %i', $id)->fetch();

        if (!$admin){
            $this->flashMessage('Učiteľ neexistuje.', 'error');
            $this->redirect('default');
        }

        return $admin;
    }

    public function actionEdit($id): Form
    {
        $admin = $this->getAdmin($id);

        /** @var  \Nette\Application\UI\Form $form */
        $form = $this['adminForm'];

        $this->template->admin = $admin;

        $form->setDefaults($admin);
        $form->addSubmit('ok', 'Upraviť');
        $form->onSuccess[] = [$this, 'adminFormEditSucceeded'];

        return $form;
    }

    public function actionAdd($id): Form
    {
        /** @var  \Nette\Application\UI\Form $form */
        $form = $this['adminForm'];

        $form->addSubmit('ok', 'Pridať');
        $form->onSuccess[] = [$this, 'adminFormAddSucceeded'];

        return $form;
    }

    public function adminFormEditSucceeded($form)
    {
        $values = $form->getValues();
        $adminId = $this->getParameter('id');

        $adminData = [
            'name' => $values->name,
            'email' => $values->email,
            'admin' => $values->admin,
        ];

        $this->userRepository->update($adminId, $adminData);

        $this->flashMessage('Učiteľ bol upravený');
        $this->redirect('default');
    }

    public function adminFormAddSucceeded($form)
    {
        $values = $form->getValues();

        $teacherData = [
            'name' => $values->name,
            'email' => $values->email,
            'admin' => $values->admin,
        ];

        $this->userRepository->insert($teacherData);

        $this->flashMessage('Učiteľ bol pridaný');
        $this->redirect('default');
    }

    public function handleDelete($id){

        $admin = $this->getAdmin($id);

        $this->userRepository->delete($admin->user_id);

        $this->flashMessage('Učiteľ bol zmazaný');

        $this->redirect('default');
    }
}
