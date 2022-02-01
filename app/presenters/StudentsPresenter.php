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

    protected function createComponentStudentForm(): Form
    {
        $form = new Form;
        $form->addText('name', 'Meno');

        $form->addText('surname', 'Priezvisko');

        $form->addText('class_id', 'Trieda');

        $form->addEmail('email', 'E-mail');
        $form->addText('phone', 'Telefón');

        return $form;
    }

    private function getStudent($id)
    {
       $student = $this->studentsRepository->findAll()->where('[st.student_id] = %i', $id)->fetch();

       if (!$student){
           $this->flashMessage('Študent neexistuje.', 'error');
           $this->redirect('default');
       }

       return $student;
    }

    public function actionEdit($id): Form
    {
        $student = $this->getStudent($id);

        /** @var  \Nette\Application\UI\Form $form */
        $form = $this['studentForm'];

        $this->template->student = $student;

        $form->setDefaults($student);
        $form->addSubmit('ok', 'Upraviť');
        $form->onSuccess[] = [$this, 'studentFormEditSucceeded'];

        return $form;
    }

    public function studentFormEditSucceeded($form)
    {
        $values = $form->getValues();
        $studentId = $this->getParameter('id');

        $studentData = [
            'name' => $values->name,
            'surname' => $values->surname,
            'email' => $values->email,
            'class_id' => $values->class_id,
            'phone' => $values->phone,
        ];

        $this->studentsRepository->update($studentId, $studentData);

        $this->flashMessage('Študent bol upravený');
        $this->redirect('default');
    }

    public function handleDelete($id){

    $student = $this->getStudent($id);


    $this->studentsRepository->delete($student->student_id);

    $this->flashMessage('Študent bol zmazaný');

    $this->redirect('default');
}

    public function createComponentStudentAddForm(): Form
    {

        $form = new Form;

        $form->addText('name', 'Meno Žiaka');

        $form->addText('surname', 'Priezvisko Žiaka');

        $form->addText('class_id', 'Trieda');

        $form->addEmail('email', 'E-Mail Žiaka');

        $form->addText('phone', 'Telefón Žiaka');

        $form->addSubmit('send', 'Odoslať');
        $form->onSuccess[] = [$this, 'StudentFormAddSucceeded'];

        return $form;
    }

    public function StudentFormAddSucceeded(Form $form, $values): void
    {
        $values = $form->getValues();

        $this->studentsRepository->insert([
            'name'      => $values->name,
            'surname'   => $values->surname,
            'class_id'   => $values->class_id,
            'email'     => $values->email,
            'phone'     => $values->phone,
        ]);

        $this->flashMessage('Študent bol pridaný');

        $this->redirect('default');


    }



}
