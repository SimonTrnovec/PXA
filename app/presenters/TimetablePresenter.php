<?php

namespace App\Presenters;

use App\Model\Repositories\ClassroomsRepository;
use App\Model\Repositories\SeatsRepository;
use App\Model\Repositories\TimetableRepository;
use App\Model\Repositories\ClassesRepository;
use App\Model\Repositories\SubjectsRepository;
use App\Model\Repositories\TimesRepository;
use App\Model\Repositories\StudentsRepository;
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

    public function actionDefault(){
        parent::startup();
        $this->template->user = $this->getUser();
        $this->template->timetables = $this->timetableRepository->findAll()
            ->select('[cl.class_name], [cr.classroom_name], [su.subject_name], [tm.time_name]')
            ->leftJoin('[classes] cl')
            ->on('[cl.class_id] = [tt.class_id]')
            ->leftJoin('[classrooms] cr')
            ->on('[tt.classroom_id] = [cr.classroom_id]')
            ->leftJoin('[subjects] su')
            ->on('[tt.subject_id] = [su.subject_id]')
            ->leftJoin('[times] tm')
            ->on('[tt.time_id] = [tm.time_id]')
            ->fetchAll();
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

        $form->addSelect('class_id', 'Trieda', $clas)
             ->setPrompt('Vyberte triedu');

        $form->addSelect('classroom_id', 'Ucebna', $classrom)
             ->setPrompt('Vyberte ucebnu');

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

        $students = $this->studentsRepository->findAll()->fetchAll();

        $sit = [];

        foreach ($students as $student){
            $sit[$student->student_id] = $student->name;
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
        $timetableId = $this->getParameter('id');
        $seatId = $this->getParameter('id');

        $timetableData = [
            'timetable_name' => $values->timetable_name,
            'class_id'       => $values->class_id,
            'classroom_id'   => $values->classroom_id,
            'subject_id'     => $values->subject_id,
            'time_id'        => $values->time_id,
        ];

        $this->timetableRepository->update($timetableId, $timetableData);

        $seatsContainerData = $values->seatsContainer;
        $seats = $this->getSeats($this->getParameter('id'));
        foreach ($seats as $seat) {
            $this->seatsRepository->update($seat->seat_id, ['student_id' => $seatsContainerData[$seat->seat_id]]);
        }

        $this->flashMessage('Rozvrh bol upravený');
        $this->redirect('default');
    }

    public function handleDelete($id){

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
            'class_id'       => $values->class_id,
            'classroom_id'   => $values->classroom_id,
            'subject_id'     => $values->subject_id,
            'time_id'        => $values->time_id,
        ]);


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
