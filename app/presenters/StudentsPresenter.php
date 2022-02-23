<?php

namespace App\Presenters;
use App\Model\Repositories\ClassesRepository;
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

    /**
     * @inject
     * @var ClassesRepository
     */
    public $classesRepository;

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

        $form->addEmail('email', 'E-mail');

        $form->addText('phone', 'Telefón');

        $classes = $this->classesRepository->findAll()->fetchAll();

        $clas = [];
        foreach ($classes as $class){
            $clas[$class->class_id] = $class->class_name;
        }

        $form->addSelect('class_id', 'Trieda', $clas)
            ->setPrompt('Vyberte triedu');;

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

    public function actionAdd($id): Form
    {
        /** @var  \Nette\Application\UI\Form $form */
        $form = $this['studentForm'];

        $form->addSubmit('ok', 'Pridať');
        $form->onSuccess[] = [$this, 'studentFormAddSucceeded'];

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
            'phone' => $values->phone,
            'class_id' => $values->class_id,
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
