<?php
class ClaseInscripcion extends ActiveRecord
{

    /**
     * Retorna las ordenes para ser paginados
     *
     * @param int $page  [requerido] pÃ¡gina a visualizar
     * @param int $ppage [opcional] por defecto 20 por pÃ¡gina
     */
    public function getClaseInscripcion($page, $ppage = 20)
    {
        return $this->paginate("page: $page", "per_page: $ppage", 'order: id desc');
    }

    //recupera todas las inscripciones del sistema
    public function getInscripcionesTodas()
    {
        return $this->find_all_by_sql('SELECT clase_inscripcion.id,clase_inscripcion.nro_inscripcion,socio.dni,concat(socio.apellido,",",socio.nombre)
        as nombreComp FROM clase_inscripcion inner join socio on clase_inscripcion.id_socio = socio.id');
    }

    //recupera las inscripciones de un socio
    public function getInscripcionesSocio($id)
    {
        return $this->find_all_by_sql('SELECT distinct clase_inscripcion.nro_inscripcion,clase_inscripcion.id
                                        FROM clase_inscripcion inner join socio on clase_inscripcion.id_socio = socio.id
                                        WHERE id_socio = ' . $id . ';');
    }

    //Para que el ID de la orden sea diferente al ID del producto use el JOIN entre las tablas clase_inscripcion y producto
    public function getClasesInscripcion($nro_inscripcion)
    {
        return $this->find_all_by_sql("SELECT clase_inscripcion.id, clase.nombre,clase.instructor,clase.dias,clase.HorarioInicio,detalle_clase_inscripcion.id as id_detalle_clase_inscripcion  
                                        FROM clase_inscripcion INNER JOIN detalle_clase_inscripcion on clase_inscripcion.id = detalle_clase_inscripcion.id_clase_inscripcion 
                                        INNER JOIN clase on clase.id = detalle_clase_inscripcion.id_clase 
                                        WHERE nro_inscripcion = " . $nro_inscripcion);
    }

    public function nuevaInscripcion($id_socio)
    {
        $this->begin();  // Iniciamos la transacción

        // Obtenemos el valor de la última inscripción
        $ultimaInscripcion = $this->find_by_sql("SELECT MAX(nro_inscripcion) as ultimaInscripcion FROM clase_inscripcion");

        // Verifica si se encontró un valor y convierte a número
        if ($ultimaInscripcion && isset($ultimaInscripcion->ultimaInscripcion)) {
            $nuevaInscripcion = intval($ultimaInscripcion->ultimaInscripcion) + 1;
        } else {
            $nuevaInscripcion = 1; // Si no se encontraron registros, comenzamos desde 1
        }

        $resultado = $this->create(   // Creamos la entrada
            "nro_inscripcion: " . $nuevaInscripcion,  // Incremento en 1 respecto de la última Inscripción disponible
            "id_socio: " . $id_socio
        );

        if ($resultado) // Si funcionó la creación
        {
            if ($this->commit())  // Finalizamos la transacción
            {
                return $nuevaInscripcion;   // Retornamos el valor de la nueva Inscripción
            }
        }

        $this->rollback(); // Si falló, retornamos al estado anterior
        return false;  // Si no salió por éxito, siempre retornamos false
    }


    // COSIGNA 1. Realiza la transaccion para guardar un nuevo producto en la base de datos y suma todas las transacciones
    public function verInscripcion($listaClases, $id_socio)
    {
        $this->begin();  // Iniciamos transacción

        $total_de_precios = 0;

        // Calculamos la suma de los precios
        foreach ($listaClases as $clase) {
            $total_de_precios += $clase->precio;
        }

        // Creamos la nueva Inscripcion
        $resultado = $this->create(
            "id_socio: " . $id_socio,  // Asigna el cliente a la Inscripcion
            "nro_inscripcion: " . $total_de_precios,
            "total_de_precios: " . $total_de_precios
        );

        if ($resultado) {
            if ($this->commit()) {
                return $total_de_precios;
            } else {
                $this->rollback();
            }
        } else {
            $this->rollback();
        }

        return false;
    }

    //CONSIGNA 2
    public function baja($id_clase_inscripcion)
    {
        $this->begin(); // Iniciamos transacción

        // Eliminamos el clase_inscripcion mediante una consulta SQL
        $resultadoInscripcion = $this->sql("DELETE FROM clase_inscripcion WHERE id_clase_inscripcion = ?", [$id_clase_inscripcion]);

        if ($resultadoInscripcion) // Si se eliminó correctamente el clase_inscripcion
        {
            if ($this->commit()) // Finaliza la transacción
            {
                return true; // Retorna true para indicar éxito
            } else {
                $this->rollback(); // Si falló, retornamos al estado anterior
            }
        } else {
            $this->rollback(); // Si falló, retornamos al estado anterior
        }

        return false; // Si no salió por éxito, retornamos false
    }

}
