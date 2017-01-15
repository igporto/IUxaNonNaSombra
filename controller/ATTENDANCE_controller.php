<?php
require_once(__DIR__ . "/../core/ViewManager.php");

require_once(__DIR__ . "/../model/ATTENDANCE.php");
require_once(__DIR__ . "/../model/ATTENDANCE_model.php");
require_once(__DIR__ . "/../model/ALUMN.php");
require_once(__DIR__ . "/../model/ALUMN_model.php");
require_once(__DIR__ . "/../model/SESSION.php");
require_once(__DIR__ . "/../model/SESSION_model.php");

require_once(__DIR__ . "/../controller/BaseController.php");



class AttendanceController extends BaseController
{


    private $attendanceMapper;
    private $alumnMapper;
    private $sessionMapper;

    public function __construct()
    {
        parent::__construct();


        $this->attendanceMapper = new AttendanceMapper();
        $this->alumnMapper = new AlumnMapper();
        $this->sessionMapper = new SessionMapper();

        // Actions controller operates in a "welcome" layout
        // different to the "default" layout where the internal
        // menu is displayed
        $this->view->setLayout("navbar");
    }

    public function add()
    {
        if (isset($_POST["submit"])) {
            //Creamos un obxecto Attendance baleiro
            $attendance = new Attendance();


            $attendance->setAlumn($this->alumnMapper->view(htmlentities(addslashes($_POST['alumn']))));

            $attendance->setAlumn($this->sessionMapper->view(htmlentities(addslashes($_POST['session']))));

            try {
               // if (!$this->attendanceMapper->attendancenameExists($attendance->getAttendancename())) {
                        $this->attendanceMapper->add($attendance);
                        $this->view->setFlash('succ_attendance_add');
                        $this->view->redirect("attendance", "show");
                //} else {
               //     $this->view->setFlash("fail_attendance_exists");
               // }
            } catch (ValidationException $ex) {
                $this->view->setFlash("erro_general");
            }
        }

        //Se non se enviou nada
        //$this->view->setLayout("navbar");
        $this->view->render("attendance", "add");
    }


    public function delete()
    {
        try {
            if (isset($_GET['codattendance'])) {
                $this->attendanceMapper->delete(htmlentities(addslashes($_GET['codattendance'])));
                $this->view->setFlash('succ_action_delete');
                $this->view->redirect("attendance", "show");
            }
        } catch (Exception $e) {
            $this->view->setFlash('erro_general');
        }
        $this->view->render("attendance", "show");
    }


    public function show()
    {
        $attendances = $this->attendanceMapper->show();
        $this->view->setVariable("attendancestoshow", $attendances);
        $this->view->render("attendance", "show");
    }

    public function view()
    {
        $attendance = $this->actionMapper->view(htmlentities(addslashes($_GET['codattendance'])));
        $this->view->setVariable("attendance", $attendance);
        $this->view->render("attendance", "view");
    }


    public function edit()
    {
        if (isset($_POST["submit"])) {

            $attendance = $this->attendanceMapper->view(htmlentities(addslashes($_REQUEST["codattendance"])));
            $attendance->setAssist($_GET['asists']);
            try {
                $this->attendanceMapper->edit($attendance);
                $this->view->setFlash("succ_attendance_edit");
                $this->view->redirect("attendance", "show");
            } catch (ValidationException $ex) {
                $this->view->setFlash("erro_general");
            }
        }
        //Se non se enviou nada
        //$this->view->setLayout("navbar");
        $this->view->render("attendance", "edit");
    }

    public function search(){
        if(isset($_POST["submit"])){

            $attendance = new Attendance();

            if(isset($_POST['alumn'])) {
                $attendance->setAlumn($this->alumnMapper->view(htmlentities(addslashes($_POST["alumn"]))));
            }else{
                $aux = new Alumn();
                $aux->setCodalumn("");
                $attendance->setAlumn($aux);
            }

            try {
                $this->view->setVariable("attendancestoshow", $this->attendanceMapper->search($attendance));
            } catch (Exception $e) {
                $this->view->setFlash("erro_general");
                $this->view->redirect("attendance", "show");
            }
            //render dado que non se pode settear a variable antes de un redirect
            $this->view->render("attendance","show");
        }else{
            $this->view->render("attendance", "search");
        }

    }

}
