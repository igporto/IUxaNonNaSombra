<!--SCRIPT DE DATATABLE-->
<!--SCRIPT DE DATATABLE-->
<?php
require_once(__DIR__."/../../core/ViewManager.php");
require_once(__DIR__ . "/../../controller/USER_controller.php");
require_once(__DIR__ . "/../../controller/CONTROLLER_controller.php");
require_once(__DIR__ . "/../../model/CONTROLLER_model.php");


include('core/language/strings/Strings_' . $_SESSION["idioma"] . '.php');

$view = ViewManager::getInstance();

//include do selector de idioma da datatable
include(__DIR__."/../../view/layouts/datatable_lang_select.php");

//include do setter de permisos do usuario
include(__DIR__."/../../view/layouts/show_flag_setter.php");

//obtemos o contido a mostrar
$injurys = $view->getVariable("injurystoshow");

?>

<div class="col-xs-12 ">

    <h1 class="page-header"><?php echo $strings['list_of_injurys'] ?></h1>

    <div class="row">


        <a href="index.php?controller=employee&action=addinjury&codemployee=<?php echo $_GET['codemployee']?>">
            <button type="button" class="btn btn-success">
                <i class="fa fa-fw fa-plus"></i>
                <?php echo $strings['create_injury'] ?>
            </button>
        </a>

    </div>



    <!--PANEL TABOA DE LISTADO-->
    <div class="row" style="margin-top: 20px">
        <div class="panel panel-default">
            <div class="panel-heading">
                <?php echo $strings['list_of_injurys']; ?>

            </div>
            <div class="panel-body">
                <table id="dataTable" class="table-responsive   table-hover" style="width:80%; margin-right: 10%; margin-left: 10%">
                    <thead>
                    <tr class="row" >
                        <!--CADA UN DE ESTES E UN CABECERO DA TABOA (TIPO "NOMBRE")-->
                        <th class="text-center"><?php echo $strings['injury_name']?></th>
                        <th class="text-center"><?php echo $strings['date_injury']?></th>
                        <th class="text-center"><?php echo $strings['date_recovery']?></th>
                        <?php
                        if(!$edit && !$delete){ ?>
                            <th class="text-center"><?php echo $strings['no_actions_to_do']?></th>
                            <?php
                        }else{
                            ?>
                            <th class="text-center"><?php echo $strings['ACTION']?></th>
                        <?php } ?>

                    </tr>
                    </thead>

                    <tbody>
                    <!--CADA UN DE ESTES E UNHA FILA-->

                    <?php
                    foreach ($injurys as $i) {

                        echo "<tr class='row text-center' ><td> ";


                        echo $i->getInjury()->getNameInjury() . "</td><td> ";

                        echo $i->getDateInjury() . "</td><td> ";
                        if($i->getDateRecovery() != NULL){
                            echo $i->getDateRecovery() ;
                        }else{
                            echo $strings['not_recovered_yet'];
                        }
                        echo "</td><td class='text-center'>";

                        if ($v) {
                            echo '<button type="button" class="btn btn-primary btn-xs';
                            echo '" data-toggle="modal" data-target="#view' . $i->getCod() . '">';

                            echo '<i class="fa fa-eye fa-fw"></i>
                                        </button>';
                        }
                        //Botón que direcciona á vista do editar
                        if ($edit) {

                            echo "<a href=index.php?controller=employee&action=editinjury&codinjuryemployee=".$i->getCod().">";
                            echo "<button class='btn btn-warning btn-xs ";
                            echo "' style='margin:2px'>";
                            echo "<i class='fa fa-edit fa-fw'></i></button></a>";

                        }
                        //Botón que direcciona á vista de eliminar
                        if ($delete) {
                            echo '<button type="button" class="btn btn-danger btn-xs';
                            echo '" data-toggle="modal" data-target="#confirmar' . $i->getCod() . '">';

                            echo '<i class="fa fa-trash-o fa-fw"></i>
                                        </button>';

                        }

                        //MODAL DE CONFIRMACIÓN DE BORRADO PARA CADA CATEGORIA
                        include(__DIR__ . '/DELETEINJURY_view.php');

                        //MODAL DE VISTA PARA CADA ACCIÓN
                        include(__DIR__ . '/VIEWINJURY_view.php');
                        echo "</td></tr>";
                    }
                    ?>

                    </tbody>
                </table><!-- fin table -->
            </div>
        </div><!-- fin panel -->
        <div class="row">

            <div class="col-xs-12">
                <div class="pull-left">
                    <a class="btn btn-default btn-md" href="index.php?controller=employee&action=show">
                        <i class="fa fa-arrow-left"></i>
                        <?php echo $strings['back'] ?></i></a>
                </div>
            </div>

        </div>

    </div><!-- fin row -->
</div><!-- fin contedor -->

