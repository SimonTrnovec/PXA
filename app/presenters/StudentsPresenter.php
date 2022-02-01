<?php

namespace App\Presenters;
use App\Model\Repositories\StudentsRepository;
use Nette;
use Nette\Application\UI\Form;


final class StudentsPresenter extends BasePresenter
{

    /**
     * @inject
     * @var StudentsRepository
     */
    public $studentsRepository;

    public function actionDefault(){
        parent::startup();
        $this->template->user = $this->getUser();
        $this->template->students = $this->studentsRepository->findAll()
            ->select('[cl.class_name]')
            ->leftJoin('[classes] cl')
            ->on('[cl.class_id] = [st.class_id]')
            ->fetchAll();

    }

    public function createComponentAddStudentForm(): Form
    {

        $form = new Form;

        $form->addText('name', 'Meno Žiaka');

        $form->addText('surname', 'Priezvisko Žiaka');

        $form->addEmail('email', 'E-Mail Žiaka');

        $form->addText('phone', 'Telefón Žiaka');

        $form->addSubmit('send', 'Odoslať');
        $form->onSuccess[] = [$this, 'addStudentFormSucceeded'];

        return $form;
    }

    public function addStudentFormSucceeded(Form $form, $values): void
    {
        $values = $form->getValues();

        $this->studentsRepository->insert([
            'name'      => $values->name,
            'surname'   => $values->surname,
            'email'     => $values->email,
            'phone'     => $values->phone,
        ]);

        $this->flashMessage('Študent bol pridaný');

        $this->redirect('this');


    }

    public function createComponentEditForm(): Form
    {

        $form = new Form;
        $form->addText('name', 'Meno Žiaka');

        $form->addText('surname', 'Priezvisko Žiaka');

        $form->addSubmit('send', 'Odoslať');

        $form->onSuccess[] = [$this, 'loginFormSucceeded'];
        return $form;
    }
}
