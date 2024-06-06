<?php
class Clase extends ActiveRecord
{

    /**
     * Retorna los clientes para ser paginados
     *
     * @param int $page  [requerido] página a visualizar
     * @param int $ppage [opcional] por defecto 20 por página
     */
    public function getClases($page, $ppage = 20)
    {
        return $this->paginate("page: $page", "per_page: $ppage", 'order: id desc');
    }

    //recupera todos los Clases
    public function getClasesTodas()
    {
        return $this->find();
    }

    //contar Clases
    public function getCantidadClases()
    {
        return $this->count();
    }
    public function getClase($id)
    {
        return $this->find($id);
    }

    public function getClasesDisponibles()
    {
        // Realiza la consulta para obtener las clases disponibles con las columnas deseadas
        return $this->find(
            "select: 'id, nombre, instructor, dias, HorarioInicio, HorarioFin'",
            "conditions: id = 1"
        );
    }
}
