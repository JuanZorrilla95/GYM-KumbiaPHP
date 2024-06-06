<?php

class DetalleClaseInscripcion extends ActiveRecord
{

    // clase index
    public function index($nro_inscripcion)
    {
        $this->nro_inscripcion = $nro_inscripcion;
        $this->listaClases = (new Clase)->getClasesTodas();
    }

    //Agrega una clase asociada a una inscripcion 
    public function agregaClase($id_clase_inscripcion, $id_clase)
    {
        $this->begin();  //iniciamos transaccion
        $datos = array("id_clase_inscripcion" => $id_clase_inscripcion, "id_clase" => $id_clase);
        if ($this->create($datos)) {
            $this->commit();
        } else {
            $this->rollback();
        }
    }
}
