<?php
//Modelo de Mapper dun Obxeto
//Encárgase de realizar todas as accións posibles sobre a db do obxeto

//Include da conexion
require_once(__DIR__ . "/../core/PDOConnection.php");

//Include do obxeto que mapeas
require_once(__DIR__ . "/../model/PAYMENT.php");

//inclues de outros obxetos que se precisen
require_once(__DIR__ . "/../model/ALUMN_model.php");
require_once(__DIR__ . "/../model/ALUMN.php");
require_once(__DIR__ . "/../model/TILL.php");

class PaymentMapper
{

    //Obtemos a instancia da conexión
    private $db;

    public function __construct()
    {
        $this->db = PDOConnection::getInstance();
    }

    //Inserta na base de datos unha tupla cos datos do obxeto $payment
    public function add(Payment $payment)
    {
        //cambiar a sentencia acorde á taboa que referencia
        //IMPORTANTE: se a PK da táboa é autoincremental, non se inserta manualmente (non se pon nos 'campo' nin nos '?')
        $stmt = $this->db->prepare("INSERT INTO pago(id_pago, fecha,cantidad,metodo_pago,pagado,tipo_cliente,dni_alum,
dni_cliente_externo) values (?,?,?,?,?,?,?,?)"); //1 ? por campo a insertar

        //cada elemento do array será insertado no correspondente ? da query
        $stmt->execute(array($payment->getIdPago(), $payment->getFecha(), $payment->getCantidad(), $payment->getMetodoPago(),
            $payment->getPagado(), $payment->getTipoCliente(), $payment->getDniAlum(), $payment->getDniClienteExterno()));

        if ($payment->getPagado() == "1" && $payment->getMetodoPago() == "cash") {
            $stmt = $this->db->prepare("INSERT INTO caja(cantidad,id_pago,fecha,concepto) values (?,?,?,?)");
            $stmt->execute(array($payment->getCantidad(), $this->db->lastInsertId(), $payment->getFecha(), "PAYMENT"));
        }

        //devolve o ID do último elemento insertado
        return $this->db->lastInsertId();
    }

    //Funcion de listar: devolve un array de todos obxetos Payment correspondentes á tabla Payment
    public function show()
    {

        $stmt = $this->db->query("SELECT * FROM pago");
        $payment_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $payments = array();

        foreach ($payment_db as $payment) {
            //se o obxeto ten atributos que referencian a outros, aquí deben crearse eses obxetos e introducilos tamén
            //introduce no array o obxeto Payment creado a partir da query
            array_push($payments, new Payment($payment["id_pago"], $payment["fecha"], $payment["cantidad"],
                $payment["metodo_pago"], $payment["pagado"], $payment["tipo_cliente"], $payment["dni_alum"],
                $payment["dni_cliente_externo"]));
        }

        //devolve o array
        return $payments;
    }


    //devolve o obxecto Payment no que o $payment_campo_id coincida co da tupla.
    public function view($payment_campo_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM pago WHERE id_pago =?");
        $stmt->execute(array($payment_campo_id));
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($payment != null) {
            return new Payment($payment["id_pago"], $payment["fecha"], $payment["cantidad"],
                $payment["metodo_pago"], $payment["pagado"], $payment["tipo_cliente"], $payment["dni_alum"],
                $payment["dni_cliente_externo"]);
        } else {
            return new Payment();
        }
    }


    //edita a tupla correspondente co id do obxecto Payment $payment
    public function edit(Payment $payment)
    {
        $stmt = $this->db->prepare("UPDATE pago set fecha =?, cantidad =?, metodo_pago =?, pagado =?, tipo_cliente =?, 
                dni_alum =?, dni_cliente_externo =? where id_pago =?");

        $stmt->execute(array($payment->getFecha(), $payment->getCantidad(), $payment->getMetodoPago(),
            $payment->getPagado(), $payment->getTipoCliente(), $payment->getDniAlum(), $payment->getDniClienteExterno(),
            $payment->getIdPago()));

        if ($payment->getPagado() == "1" && $payment->getMetodoPago() == "cash") {
            $stmt = $this->db->prepare("INSERT INTO caja(cantidad,id_pago) values (?,?)");
            $stmt->execute(array($payment->getCantidad(), $this->db->lastInsertId()));
        }
    }


    //borra sobre a taboa pago a tupla con id igual a o do obxeto pasado
    public function delete(Payment $payment)
    {
        $stmt = $this->db->prepare("DELETE from pago WHERE id_pago =?");
        $stmt->execute(array($payment->getIdPago()));
    }

    //Comproba se existe un perfil con ese nome
    public function paymentIdExists($paymentId)
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM pago where id_pago =?");
        $stmt->execute(array($paymentId));

        if ($stmt->fetchColumn() > 0) {
            return true;
        }
    }

    public function search(Payment $payment)
    {
        $stmt = $this->db->prepare("SELECT * FROM pago WHERE cantidad like ? AND metodo_pago like ? AND tipo_cliente like ? 
                                        AND pagado like ? AND dni_alum like ? AND dni_cliente_externo like ?");
        if ($payment->getDniAlum() != "" && $payment->getDniClienteExterno() == NULL) {
            echo "Primero - ";
            $stmt = $this->db->prepare("SELECT * FROM pago WHERE cantidad like ? AND metodo_pago like ? AND tipo_cliente like ? 
                                        AND pagado like ? AND dni_alum like ?");
            $stmt->execute(array("%" . $payment->getCantidad() . "%",
                "%" . $payment->getMetodoPago() . "%", "%" . $payment->getTipoCliente() . "%",
                "%" . $payment->getPagado() . "%", "%" . $payment->getDniAlum() . "%"));
        } else if ($payment->getDniAlum() == NULL && $payment->getDniClienteExterno() != "") {
            echo "Segundo - ";
            $stmt = $this->db->prepare("SELECT * FROM pago WHERE cantidad like ? AND metodo_pago like ? AND tipo_cliente like ? 
                                        AND pagado like ? AND dni_cliente_externo like ?");
            $stmt->execute(array("%" . $payment->getCantidad() . "%",
                "%" . $payment->getMetodoPago() . "%", "%" . $payment->getTipoCliente() . "%",
                "%" . $payment->getPagado() . "%", "%" . $payment->getDniClienteExterno() . "%"));
        } else {
            echo "Tercero - ";
            $stmt = $this->db->prepare("SELECT * FROM pago WHERE cantidad like ? AND metodo_pago like ? AND tipo_cliente like ? 
                                        AND pagado like ?");
            $stmt->execute(array("%" . $payment->getCantidad() . "%",
                "%" . $payment->getMetodoPago() . "%", "%" . $payment->getTipoCliente() . "%",
                "%" . $payment->getPagado() . "%"));
        }


        $payments_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $payments = array();
        foreach ($payments_db as $p) {
            array_push($payments, new Payment($p["id_pago"], $p["fecha"], $p["cantidad"],
                $p["metodo_pago"], $p["pagado"], $p["tipo_cliente"], $p["dni_alum"],
                $p["dni_cliente_externo"]));
        }
        return $payments;
    }

    public function tillspend(Till $till)
    {
        $stmt = $this->db->prepare("INSERT INTO caja(cantidad,id_pago,fecha,concepto) values (?,?,?,?)");
        $stmt->execute(array($till->getCantidad(), 0, $till->getFecha(), $till->getConcepto()));
    }

    public function tillwithdrawal(Till $till)
    {
        $stmt = $this->db->prepare("INSERT INTO caja(cantidad,id_pago,fecha,concepto) values (?,?,?,?)");
        $stmt->execute(array($till->getCantidad(), 0, $till->getFecha(), "WITHDRAWAL"));
    }

    public function tillconsult()
    {

        $stmt = $this->db->query("SELECT * FROM caja");
        $till_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $tills = array();

        foreach ($till_db as $till) {
            //se o obxeto ten atributos que referencian a outros, aquí deben crearse eses obxetos e introducilos tamén
            //introduce no array o obxeto Payment creado a partir da query
            array_push($tills, new Till($till["id_caja"], $till["cantidad"], $till["id_pago"], $till["fecha"], $till["concepto"]));
        }

        //devolve o array
        return $tills;
    }

    public function pending()
    {

        $stmt = $this->db->query("SELECT * FROM pago WHERE pagado = 0");
        $payment_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $payments = array();

        foreach ($payment_db as $payment) {
            //se o obxeto ten atributos que referencian a outros, aquí deben crearse eses obxetos e introducilos tamén
            //introduce no array o obxeto Payment creado a partir da query
            array_push($payments, new Payment($payment["id_pago"], $payment["fecha"], $payment["cantidad"],
                $payment["metodo_pago"], $payment["pagado"], $payment["tipo_cliente"], $payment["dni_alum"],
                $payment["dni_cliente_externo"]));
        }

        //devolve o array
        return $payments;
    }

    public function getByDate(Payment $payment){

        $stmt = $this->db->prepare("SELECT * FROM pago WHERE fecha = ? AND dni_alum = ?");
        $stmt->execute(array($payment->getFecha(), $payment->getDniAlum()));
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($payment != null) {
            return $this->view($payment['id_pago']);
        } else {
            return new Payment();
        }

    }
}
