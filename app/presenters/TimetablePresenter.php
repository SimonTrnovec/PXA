<?php

namespace App\Presenters;

use App\Model\Repositories\ClassGroupsRepository;
use App\Model\Repositories\ClassroomsRepository;
use App\Model\Repositories\SeatsRepository;
use App\Model\Repositories\TimetableRepository;
use App\Model\Repositories\ClassesRepository;
use App\Model\Repositories\SubjectsRepository;
use App\Model\Repositories\TimesRepository;
use App\Model\Repositories\StudentsRepository;
use Dibi\Row;
use Nette;
use Nette\Application\UI\Form;


final class TimetablePresenter extends BasePresenter
{

    /**
     * @inject
     * @var TimetableRepository
     */
    public $timetableRepository;

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
     * @inject
     * @var SubjectsRepository
     */
    public $subjectsRepository;

    /**
     * @inject
     * @var TimesRepository
     */
    public $timesRepository;

    /**
     * @inject
     * @var SeatsRepository
     */
    public $seatsRepository;

    /**
     * @inject
     * @var StudentsRepository
     */
    public $studentsRepository;

    /**
     * @inject
     * @var ClassGroupsRepository
     */
    public $classGroupRepository;

    /**
     * @persistent
     * @var array
     */
    public $filter = [];

    /**
     * @var Row
     */
    protected $items = [];

    protected function createComponentFilterForm()
    {
        $form = new Form;

        $form->addGroup();
        $classes = $this->classesRepository->findAll()->fetchAll();

        $clas = [];
        foreach ($classes as $class){
            $clas[$class->class_id] = $class->class_name;
        }

        $classrooms = $this->classroomsRepository->findAll()->fetchAll();

        $classrom = [];

        foreach ($classrooms as $classroom){
            $classrom[$classroom->classroom_id] = $classroom->classroom_name;
        }

        $subjects = $this->subjectsRepository->findAll()->fetchAll();

        $subj = [];

        foreach ($subjects as $subject){
            $subj[$subject->subject_id] = $subject->subject_name;
        }

        $times = $this->timesRepository->findAll()->fetchAll();


        $thime = [];

        foreach ($times as $time){
            $thime[$time->time_id] = $time->time_name;
        }

        $form->addSelect('class_id', 'Trieda', $clas)
            ->setPrompt('Vyberte Triedu');

        $form->addSelect('classroom_id', 'Ucebna', $classrom)
            ->setPrompt('Vyberte Ucebnu');

        $form->addSelect('subject_id', 'Predmet', $subj)
            ->setPrompt('Vyberte Predmet');

        $form->addSelect('time_id', 'Čas', $thime)
            ->setPrompt('Vyberte Čas');

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

    public function actionDefault(){
        parent::startup();
        $itemsQuery = $this->timetableRepository->findAll()
            ->select('[cl.class_name], [cr.classroom_name], [su.subject_name], [tm.time_name]')
            ->leftJoin('[classes] cl')
            ->on('[cl.class_id] = [tt.class_id]')
            ->leftJoin('[classrooms] cr')
            ->on('[tt.classroom_id] = [cr.classroom_id]')
            ->leftJoin('[subjects] su')
            ->on('[tt.subject_id] = [su.subject_id]')
            ->leftJoin('[times] tm')
            ->on('[tt.time_id] = [tm.time_id]');

        if (isset($this->filter['class_id'])) {
            $itemsQuery->where('[tt.class_id] LIKE %~like~', $this->filter['class_id']);
        }

        if (isset($this->filter['classroom_id'])) {
            $itemsQuery->where('[tt.classroom_id] LIKE %~like~', $this->filter['classroom_id']);
        }
        if (isset($this->filter['subject_id'])) {
            $itemsQuery->where('[tt.subject_id] LIKE %~like~', $this->filter['subject_id']);
        }
        if (isset($this->filter['time_id'])) {
            $itemsQuery->where('[tt.time_id] LIKE %~like~', $this->filter['time_id']);
        }

        $this->items = $itemsQuery->fetchAssoc('timetable_id');
    }

    public function renderDefault()
    {
        $this->template->timetables = $this->items;

    }

    protected function createComponentTimetableAddForm(): Form
    {
        $form = new Form;
        $form->addText('timetable_name', 'Názov Rozvrhu');

        $classes = $this->classesRepository->findAll()->fetchAll();

        $clas = [];
        foreach ($classes as $class){
            $clas[$class->class_id] = $class->class_name;
        }

        $classrooms = $this->classroomsRepository->findAll()->fetchAll();

        $classrom = [];

        foreach ($classrooms as $classroom){
            $classrom[$classroom->classroom_id] = $classroom->classroom_name;
        }

        $subjects = $this->subjectsRepository->findAll()->fetchAll();

        $subj = [];

        foreach ($subjects as $subject){
            $subj[$subject->subject_id] = $subject->subject_name;
        }

        $times = $this->timesRepository->findAll()->fetchAll();


        $thime = [];

        foreach ($times as $time){
            $thime[$time->time_id] = $time->time_name;
        }

        $form->addMultiSelect('class_id', 'Trieda', $clas);

        $form->addSelect('classroom_id', 'Ucebna', $classrom)
            ->setPrompt('Vyberte Učebňu')
            ->setRequired('Prosím vyberte učebnu.');

        $form->addSelect('subject_id', 'Predmet', $subj)
            ->setPrompt('Vyberte Predmet');

        $form->addSelect('time_id', 'Čas', $thime)
            ->setPrompt('Vyberte Čas');


        $students = $this->studentsRepository->findAll()->fetchAll();

        $sit = [];

        foreach ($students as $student){
            $sit[$student->student_id] = $student->name;
        }

        return $form;
    }



    protected function createComponentTimetableEditForm(): Form
    {
        $form = new Form;
        $form->addText('timetable_name', 'Názov Rozvrhu');

        $classes = $this->classesRepository->findAll()->fetchAll();

        $clas = [];
        foreach ($classes as $class){
            $clas[$class->class_id] = $class->class_name;
        }

        $classrooms = $this->classroomsRepository->findAll()->fetchAll();

        $classrom = [];

        foreach ($classrooms as $classroom){
            $classrom[$classroom->classroom_id] = $classroom->classroom_name;
        }

        $subjects = $this->subjectsRepository->findAll()->fetchAll();

        $subj = [];

        foreach ($subjects as $subject){
            $subj[$subject->subject_id] = $subject->subject_name;
        }

        $times = $this->timesRepository->findAll()->fetchAll();


        $thime = [];

        foreach ($times as $time){
            $thime[$time->time_id] = $time->time_name;
        }

        $form->addSelect('class_id', 'Trieda', $clas)
            ->setPrompt('Vyberte triedu');

        $form->addSelect('classroom_id', 'Ucebna', $classrom)
            ->setPrompt('Vyberte ucebnu');

        $form->addSelect('subject_id', 'Predmet', $subj)
            ->setPrompt('Vyberte Predmet');

        $form->addSelect('time_id', 'Čas', $thime)
            ->setPrompt('Vyberte Čas');

        $timetableValues = $this->getTimetable($this->getParameter('id'));

        $timetableId =  [
            'timetable_id' => $timetableValues->timetable_id,
        ];

        $classGroups = $this->classGroupRepository->findAll()->removeClause('select')->where('[cg.timetable_id] = %i', $timetableId)->select('[cg.class_id]')->fetchAll();

        if ($classGroups){
            $students = $this->studentsRepository->findAll()->where('[st.class_id] IN %in', $classGroups)->orderBy('[st.name] ASC')->fetchAll();
        } else {
            $students = $this->studentsRepository->findAll()->orderBy('[st.name] ASC')->fetchAll();
        }

        $sit = [];

        foreach ($students as $student){
            $sit[$student->student_id] = $student->name .' '. $student->surname;
        }

        $seatsContainer = $form->addContainer('seatsContainer');

        $seats = $this->getSeats($this->getParameter('id'));
        foreach ($seats as $seat) {
            $seatsContainer->addSelect($seat->seat_id, 'Miesto', $sit)
                ->setPrompt('-');
        }


        return $form;
    }

    private function getTimetable($id)
    {
        $timetable = $this->timetableRepository->findAll()->where('[tt.timetable_id] = %i', $id)->fetch();

        if (!$timetable){
            $this->flashMessage('Rozvrh neexistuje.', 'error');
            $this->redirect('default');
        }

        return $timetable;
    }

    private function getSeats($id)
    {

        $seats = $this->seatsRepository->findAll()
            ->where('[se.timetable_id] = %i' , $id)
            ->select('[st.name], [st.surname]')
            ->leftJoin('[students] st')
            ->on('[se.student_id] = [st.student_id]')->orderBy(['order'])
            ->fetchAll();

        if (!$seats){
            $this->flashMessage('Miesto neexistuje.', 'error');
            $this->redirect('default');
        }

        return $seats;
    }

    public function actionEdit($id)
    {
        if($this->isTeacher()){
            $this->redirect('BackendAuth:login');
        }

        $timetable = $this->getTimetable($id);
        $seats = $this->getSeats($id);

        /** @var  \Nette\Application\UI\Form $form */
        $form = $this['timetableEditForm'];

        $this->template->timetable = $timetable;

        $this->template->timetables = $this->timetableRepository->findAll()
            ->where('[tt.timetable_id] = %i' , $id)
            ->select('[cr.classroom_name], [cr.height], [cr.width]')
            ->leftJoin('[classrooms] cr')
            ->on('[tt.classroom_id] = [cr.classroom_id]')
            ->fetchAll();

        $this->template->seats = $this->seatsRepository->findAll()
            ->where('[se.timetable_id] = %i' , $id)
            ->select('[st.name], [st.surname]')
            ->leftJoin('[students] st')
            ->on('[se.student_id] = [st.student_id]')->orderBy(['order'])
            ->fetchAll();

        $this->template->students = $this->studentsRepository->findAll()->fetchAll();

        $seats = $this->getSeats($id);

        $seatsContainer = $form['seatsContainer'];
        foreach ($seats as $seat){
            $seatsContainer[$seat->seat_id]->setDefaultValue($seat->student_id);
        }

        $form->setDefaults($timetable);
        $form->addSubmit('ok', 'Upraviť');
        $form->onSuccess[] = [$this, 'timetableFormEditSucceeded'];
    }

    public function actionAdd()
    {
        if($this->isTeacher()){
            $this->redirect('BackendAuth:login');
        }

        /** @var  \Nette\Application\UI\Form $form */
        $form = $this['timetableAddForm'];

        $form->addSubmit('ok', 'Pridať');
        $form->onSuccess[] = [$this, 'timetableFormAddSucceeded'];

        return $form;
    }

    public function actionDetail($id)
    {
        $this->template->timetables = $this->timetableRepository->findAll()
            ->where('[tt.timetable_id] = %i' , $id)
            ->select('[cr.classroom_name], [cr.height], [cr.width]')
            ->leftJoin('[classrooms] cr')
            ->on('[tt.classroom_id] = [cr.classroom_id]')
            ->fetchAll();

        $this->template->seats = $this->seatsRepository->findAll()
            ->where('[se.timetable_id] = %i' , $id)
            ->select('[st.name], [st.surname]')
            ->leftJoin('[students] st')
            ->on('[se.student_id] = [st.student_id]')->orderBy(['order'])
            ->fetchAll();
    }

    public function timetableFormEditSucceeded($form)
    {
        $values = $form->getValues();

        $seatsContainerData = $values->seatsContainer;
        $seats = $this->getSeats($this->getParameter('id'));
        foreach ($seats as $seat) {
            $this->seatsRepository->update($seat->seat_id, ['student_id' => $seatsContainerData[$seat->seat_id]]);
        }

        $this->flashMessage('Rozvrh bol upravený');
        $this->redirect('default');
    }

    public function handleDelete($id){

        if($this->isTeacher()){
            $this->redirect('BackendAuth:login');
        }

        $timetable = $this->getTimetable($id);

        $this->timetableRepository->delete($timetable->timetable_id);

        $this->flashMessage('Rozvrh bol zmazaný');

        $this->redirect('default');
    }

    public function timetableFormAddSucceeded(Form $form, $values): void
    {

        $values = $form->getValues();
        $timetableId = $this->timetableRepository->insert([
            'timetable_name' => $values->timetable_name,
            'classroom_id'   => $values->classroom_id,
            'subject_id'     => $values->subject_id,
            'time_id'        => $values->time_id,
        ]);

        $classes = $values->class_id;
        foreach ($classes as $class){
            $classGroupId = $this->classGroupRepository->insert([
                'timetable_id' => $timetableId,
                'class_id' => $class
            ]);
        }

        $sizes =  $this->classroomsRepository->findAll()
            ->where('[cr.classroom_id] = %i' , $values->classroom_id)->fetchAll();

        foreach ($sizes as $size){
            $allseats = $size->height * $size->width;
        }

        for ($i = 1; $i <= $allseats; $i++){

        $this->seatsRepository->insert([
           'order' => $i,
           'timetable_id' => $timetableId,
        ]);
        }

        $this->flashMessage('Rozvrh bol prdaný');

        $this->redirect('default');

    }
}
