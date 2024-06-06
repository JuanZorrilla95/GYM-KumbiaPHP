<?php
class Turno extends ActiveRecord
{

    /**
     * Retorna los clientes para ser paginados
     *
     * @param int $page  [requerido] página a visualizar
     * @param int $ppage [opcional] por defecto 20 por página
     */
    public function getTurnos($page, $ppage = 20)
    {
        return $this->paginate_by_sql('SELECT

        -- alumno.nombre AS alumno_nombre,
        -- alumno.apellido AS alumno_apellido,
        socio.nombre AS socio_nombre,
        socio.apellido AS socio_apellido,
        socio.dni AS socio_dni, 
        clase.nombre,
        turno.*
        
        FROM turno
        
        
        LEFT JOIN socio ON turno.id_socio = socio.id
        LEFT JOIN clase ON turno.id_clase = clase.id

        ORDER BY
        turno.id_socio,
        turno.id_clase
        
        ', "page: $page", "per_page: $ppage");
    }

    //Consigna 3. Recupera todos los clases
    public function getTurnosTodos()
    {
        return $this->find();
    }
    public function getTurno($id)
    {
        return $this->find($id);
    }
    public function getTurnosFiltrados($id_clase, $fecha, $id_socio)
    {
        return $this->find('conditions: id_clase = ' . $id_clase . ' AND fecha = "' . $fecha . '" AND id_socio = ' . $id_socio);
    }

    //Consigna 3
    public function existeSolapamientoDeOtrasClases($claseSeleccionada, $horarioInicio, $horarioFin)
    {
        return $this->count("id_clase != $claseSeleccionada AND
            ((HorarioInicio <= '$horarioInicio' AND HorarioFin >= '$horarioInicio') OR
            (HorarioInicio <= '$horarioFin' AND HorarioFin >= '$horarioFin'))") > 0;
    }

    //Consiga 7. Recupera los turnos de un socio (solapa Consultar asistencias)
    public function getTurnosSocio($id)
    {

        return $this->find_all_by_sql('SELECT distinct turno.id_socio,turno.id
                                        FROM turno inner join socio on turno.id_socio = socio.id
                                        WHERE id_socio = ' . $id . ';');
    }
    //contar turnos
    public function getCantidadTurnos()
    {
        return $this->count();
    }
    //funcion para buscar turnos por socio
    public function getTurnosPorSocio($claseId,  $socioId)
    {
        // Consulta para obtener los horarios ocupados del socio para la clase específica.
        return $this->find();
        $conditions = "id_clase = ? AND id_socio = ?";
        $bind = [$claseId,  $socioId];
        return $this->find($conditions, $bind);
    }
    public function getTurnosPorClase($id_clase)
    {
        return $this->find_all_by_sql('
        SELECT turno.id, turno.fecha, turno.id_clase, socio.apellido as socio_apellido, socio.nombre as socio_nombre, socio.dni as socio_dni
        FROM turno
        INNER JOIN socio ON turno.id_socio = socio.id
        WHERE turno.id_clase = ' . $id_clase . ';
    ');
    }

    //Para que el ID de la orden sea diferente al ID del producto use el JOIN entre las tablas clase_inscripcion y producto
    // public function getAsistenciasTurno($nro_turno)
    // {
    //     return $this->find_all_by_sql(
    //         "
    //     SELECT 
    //         turno.id,
    //         turno.id_socio,
    //         turno.id_clase,
    //         turno.HorarioInicio,
    //         turno.HorarioFin,
    //         turno.fecha,
    //         turno.nro_turno,
    //         detalle_turno_asistencia.id AS id_detalle_turno_asistencia  
    //     FROM 
    //         turno 
    //     INNER JOIN 
    //         detalle_turno_asistencia ON turno.id = detalle_turno_asistencia.id_turno_asistencia 
    //     INNER JOIN 
    //         turno AS t2 ON t2.id = detalle_turno_asistencia.id_clase 
    //     WHERE 
    //         turno.nro_turno = " . $nro_turno
    //     );
    // }

    // public function verAsistencia($listaTurnos, $id_socio)
    // {
    //     $this->begin();  // Iniciamos transacción

    //     $total_de_precios = 0;

    //      Calculamos la suma de los precios
    //     foreach ($listaTurnos as $turno) {
    //         $total_de_precios += $turno->precio;
    //     }

    //      Creamos la nueva Inscripcion
    //     $resultado = $this->create(
    //         "id_socio: " . $id_socio,  // Asigna el cliente a la Inscripcion
    //         "nro_inscripcion: " . $total_de_precios,
    //         "total_de_precios: " . $total_de_precios
    //     );

    //     if ($resultado) {
    //         if ($this->commit()) {
    //             return $total_de_precios;
    //         } else {
    //             $this->rollback();
    //         }
    //     } else {
    //         $this->rollback();
    //     }

    //     return false;
    // }
}
