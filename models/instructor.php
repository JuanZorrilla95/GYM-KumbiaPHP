<?php
class Instructor extends ActiveRecord
{

    /**
     * Retorna los Alumnos para ser paginados
     * @param int $page  [requerido] página a visualizar
     * @param int $ppage [opcional] por defecto 20 por página
     *  Metodo propio de la clase ActiveRecord
     */

    public function getInstructores($page, $ppage = 20)
    {
        return $this->paginate("page: $page", "per_page: $ppage", 'order: id desc');
    }

    public function getInstructoresTodos()
    {
        return $this->find();
    }

    public function getInstructor($id)
    {
        return $this->find($id);
    }

    public function getCantidadInstructores()
    {
        return $this->count();
    }
}
