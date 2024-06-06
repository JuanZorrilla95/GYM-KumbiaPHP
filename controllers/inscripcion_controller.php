<?php
class InscripcionController extends AppController
{
    public $limit_params = FALSE;
    /**
     * Obtiene una lista para paginar los menús
     *
     * @param int $page [opcional]
     */
    public function index($page = 1)
    {
        $this->listClaseInscripcion = (new ClaseInscripcion)->getInscripcionesTodas();
    }

    //recuperar la inscripción de un socio por su id
    public function getInscripcionSocio($id)
    {
        $this->listClaseInscripcion = (new ClaseInscripcion)->getInscripcionesSocio($id);
        $this->id = $id;
    }

    //recupera la informacion de una inscripcion por su numero de inscripcion. Consigna 1
    public function verInscripcion($nro_inscripcion)
    {
        $this->listClaseInscripcion = (new ClaseInscripcion)->getClasesInscripcion($nro_inscripcion); //obtiene las clases inscriptas
        $this->listaClases = (new Clase)->getClasesTodas(); //listado de los productos disponibles para agregar
        $this->nro_inscripcion = $nro_inscripcion;
        $this->totalInscripcion = 0;
        foreach ($this->listClaseInscripcion as $claseInscripcion) { //recorre los productos de la orden
            $this->totalInscripcion += $claseInscripcion->precio; //suma el precio de los productos
        }
    }

    //genera una nueva inscripcion
    public function nuevaInscripcion($id)
    {
        $this->nro_inscripcion = (new ClaseInscripcion)->nuevaInscripcion($id);
        if ($this->nro_inscripcion === false) {
            Flash::error('Falló Operación');
        } else {
            $this->listClaseInscripcion = new ClaseInscripcion($this->nro_inscripcion);
            Flash::valid('Operación exitosa');
        }
    }

    //Agrega una clase a una inscripcion
    public function agregarClase($nro_inscripcion)
    {

        if (Input::hasPost('clases')) {
            $nuevo_elemnto_inscripcion = new DetalleClaseInscripcion(); //creo un elemento de detalle orden

            $elementoAgregado = Input::post('clases'); //recupero el elelemnto agregado

            $unaInscripcion = (new ClaseInscripcion())->find_first("conditions: nro_inscripcion= " . $nro_inscripcion); //recupero de la base de datos el id de la inscripcion por su nro_inscripcion

            $nuevo_elemnto_inscripcion->agregaClase($unaInscripcion->id, $elementoAgregado);
        }

        Redirect::to("inscripcion/verInscripcion/" . $nro_inscripcion);
    }

    //CONSIGNA 2
    public function baja($id_clase_inscripcion)
    {

        $inscripcion = new ClaseInscripcion();    //inicio el objeto Model 
        $detalleInscripcion = new DetalleClaseInscripcion();   //inicio el objeto Model

        $inscripcion->begin();    //inicio una transacción
        $detalleInscripcion->begin();   //inicio una transacción

        $resultadoDetalle = $detalleInscripcion->delete_all("id_clase_inscripcion =" . $id_clase_inscripcion);

        if ($resultadoDetalle) {
            $resultadoInscripcion = $inscripcion->delete($id_clase_inscripcion);
            if ($resultadoInscripcion) {
                $detalleInscripcion->commit();
                $inscripcion->commit();
            } else {
                $inscripcion->rollback();
                $detalleInscripcion->rollback();
            }
        } else {
            $detalleInscripcion->rollback();
        }

        Redirect::to("inscripcion");
    }

    //CONSIGNA 3
    public function bajaInscripcionClase($nro_inscripcion, $id_detalle_clase_inscripcion)
    {

        $resultado = (new DetalleClaseInscripcion)->delete($id_detalle_clase_inscripcion);
        if ($resultado) {
            Flash::valid('Operación exitosa');
        } else {
            Flash::error('Falló Operación');
        }

        Redirect::to("inscripcion/verInscripcion/" . $nro_inscripcion);
    }
}
