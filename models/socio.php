<?php
class Socio extends ActiveRecord
{

    /**
     * Retorna los Socios para ser paginados
     * @param int $page  [requerido] página a visualizar
     * @param int $ppage [opcional] por defecto 20 por página
     *  Metodo propio de la clase ActiveRecord
     */

    public function getSocios($page, $ppage = 20)
    {
        return $this->paginate("page: $page", "per_page: $ppage", 'order: id desc');
    }

    public function getSociosTodos()
    {
        return $this->find();
    }

    public function getSocio($id)
    {
        return $this->find($id);
    }
    
    
    public function getCantidadSocios()
    {
        return $this->count();
    }
}
