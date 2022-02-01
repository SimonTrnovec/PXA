<?php

namespace App\Presenters;
use App\Model\Repositories\TeachersRepository;
use App\Model\Repositories\ClassesRepository;
use App\Model\Repositories\ClassroomsRepository;

use Nette;
use Nette\Application\UI\Form;


final class TeachersPresenter extends BasePresenter
{

    /**
     * @inject
     * @var TeachersRepository
     */
    public $teachersRepository;

    /**
     * @inject
     * @var ClassesRepository
     */
    public $classesRepository;

    /**
     * @inject
     * @var ClassroomsRepository
     */
    public $classroomsRepository;

    public function actionDefault(){
        parent::startup();
        $this->template->user = $this->getUser();
        $this->template->teachers = $this->teachersRepository->findAll()
            ->select('[cl.class_name], [cr.classroom_name]')
            ->leftJoin('[classes] cl')
            ->on('[cl.class_id] = [te.class_id]')
            ->leftJoin('[classrooms] cr')
            ->on('[te.classroom_id] = [cr.classroom_id]')
            ->fetchAll();



    }

    protected function createComponentTeacherForm(): Form
    {
        $form = new Form;
        $form->addText('name', 'Meno');

        $form->addText('surname', 'Priezvisko');

        $form->addEmail('email', 'E-mail');

        $classes = $this->classesRepository->findAll()->fetchAll();

        $clas = [];
        foreach ($classes as $class){
            $clas[$class->class_id] = $class->class_name;
        }

        $form->addSelect('class_id', 'Trieda', $clas)
            ->setPrompt('-');;

        $classrooms = $this->classroomsRepository->findAll()->fetchAll();

        $classroomavalible = [];
        foreach ($classrooms as $classroom){
            $classroomavalible[$classroom->classroom_id] = $classroom->classroom_name;
        }

        $form->addSelect('classroom_id', 'Miesto', $classroomavalible)
            ->setPrompt('-');;

        return $form;
    }

    private function getTeacher($id)
    {
        $teacher = $this->teachersRepository->findAll()->where('[te.teacher_id] = %i', $id)->fetch();

        if (!$teacher){
            $this->flashMessage('Učiteľ neexistuje.', 'error');
            $this->redirect('default');
        }

        return $teacher;
    }

    public function actionEdit($id): Form
    {
        $teacher = $this->getTeacher($id);

        /** @var  \Nette\Application\UI\Form $form */
        $form = $this['teacherForm'];

        $this->template->teacher = $teacher;

        $form->setDefaults($teacher);
        $form->addSubmit('ok', 'Upraviť');
        $form->onSuccess[] = [$this, 'teacherFormEditSucceeded'];

        return $form;
    }

    public function actionAdd($id): Form
    {
        /** @var  \Nette\Application\UI\Form $form */
        $form = $this['teacherForm'];

        $form->addSubmit('ok', 'Pridať');
        $form->onSuccess[] = [$this, 'teacherFormAddSucceeded'];

        return $form;
    }

    public function teacherFormEditSucceeded($form)
    {
        $values = $form->getValues();
        $teacherId = $this->getParameter('id');

        $teacherData = [
            'name' => $values->name,
            'surname' => $values->surname,
            'email' => $values->email,
            'class_id' => $values->class_id,
            'classroom_id' => $values->classroom_id,
        ];

        $this->teachersRepository->update($teacherId, $teacherData);

        $this->flashMessage('Učiteľ bol upravený');
        $this->redirect('default');
    }

    public function teacherFormAddSucceeded($form)
    {
        $values = $form->getValues();

        $teacherData = [
            'name' => $values->name,
            'surname' => $values->surname,
            'email' => $values->email,
            'class_id' => $values->class_id,
            'classroom_id' => $values->classroom_id,
        ];

        $this->teachersRepository->insert($teacherData);

        $this->flashMessage('Učiteľ bol pridaný');
        $this->redirect('default');
    }

    public function handleDelete($id){

        $teacher = $this->getTeacher($id);

        $this->teachersRepository->delete($teacher->teacher_id);

        $this->flashMessage('Učiteľ bol zmazaný');

        $this->redirect('default');
    }
}
