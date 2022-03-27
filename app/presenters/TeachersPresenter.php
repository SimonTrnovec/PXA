<?php

namespace App\Presenters;
use App\Model\Repositories\TeachersRepository;
use App\Model\Repositories\ClassesRepository;
use App\Model\Repositories\ClassroomsRepository;

use Dibi\Row;
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

    /**
     * @persistent
     * @var array
     */
    public $filter = [];

    /**
     * @var Row
     */
    protected $items = [];

    public function startup()
    {
        parent::startup();

        if($this->isTeacher()){
            $this->redirect('BackendAuth:login');
        }
    }

    public function actionDefault(){
        parent::startup();
        $this->template->user = $this->getUser();
        $itemsQuery = $this->teachersRepository->findAll()
            ->select('[cl.class_name], [cr.classroom_name]')
            ->leftJoin('[classes] cl')
            ->on('[cl.class_id] = [te.class_id]')
            ->leftJoin('[classrooms] cr')
            ->on('[te.classroom_id] = [cr.classroom_id]');

        if (isset($this->filter['name'])) {
            $itemsQuery->where('[te.name] LIKE %~like~', $this->filter['name']);
        }
        if (isset($this->filter['surname'])) {
            $itemsQuery->where('[te.surname] LIKE %~like~', $this->filter['surname']);
        }
        if (isset($this->filter['email'])) {
            $itemsQuery->where('[te.email] LIKE %~like~', $this->filter['email']);
        }
        if (isset($this->filter['class_id'])) {
            $itemsQuery->where('[te.class_id] LIKE %~like~', $this->filter['class_id']);
        }
        if (isset($this->filter['class_id'])) {
            $itemsQuery->where('[te.class_id] LIKE %~like~', $this->filter['class_id']);
        }

        $this->items = $itemsQuery->fetchAssoc('teacher_id');
    }

    public function renderDefault()
    {
        $this->template->teachers = $this->items;
    }

    protected function createComponentFilterForm()
    {
        $form = new Form;

        $form->addGroup();

        $form->addText('name', 'Meno');

        $form->addText('surname', 'Priezvisko');

        $form->addText('email', 'E-mail');

        $classes = $this->classesRepository->findAll()->fetchAll();

        $clas = [];
        foreach ($classes as $class){
            $clas[$class->class_id] = $class->class_name;
        }

        $form->addSelect('class_id', 'Trieda', $clas)
            ->setPrompt('Vyberte triedu');

        $classrooms = $this->classroomsRepository->findAll()->fetchAll();

        $classrom = [];

        foreach ($classrooms as $classroom){
            $classrom[$classroom->classroom_id] = $classroom->classroom_name;
        }

        $form->addSelect('classroom_id', 'Ucebna', $classrom)
            ->setPrompt('Vyberte ucebnu');

        $form->setDefaults($this->filter);

        $form->addSubmit('ok', 'Filtrovať');
        $form->addSubmit('cancel', 'Zrušiť');

        $form->onSuccess[] = [$this, 'filterFormSucceeded'];

        return $form;
    }

    public function filterFormSucceeded($form)
    {
        if($form['cancel']->isSubmittedBy()) {
            $this->redirect('default', [
                'filter'    => NULL,
            ]);
        } else {
            $values = $form->getValues();

            $filteredValues = array_filter((array) $values);


            $this->redirect('default', [
                'filter'    => $filteredValues,
            ]);
        }

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
            ->setPrompt('Vyberte triedu');;

        $classrooms = $this->classroomsRepository->findAll()->fetchAll();

        $classroomavalible = [];
        foreach ($classrooms as $classroom){
            $classroomavalible[$classroom->classroom_id] = $classroom->classroom_name;
        }

        $form->addSelect('classroom_id', 'Učebňa', $classroomavalible)
            ->setPrompt('Vyberte učebňu');;

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
