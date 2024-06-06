<?php
class SocioController extends AppController
{

    /**
     * Obtiene una lista para paginar los menús
     *
     * @param int $page [opcional]
     */
    public function index($page = 1)
    {
        $this->listSocio = (new Socio)->getSocios($page);
    }

    /**
     * Crea un Registro
     */
    public function alta()
    {
        //Establecer lista de categorías
        $this->categorias = ['Musculacion', 'Aerobicos', 'Box', 'Crossfit', 'Functional'];

        try {
            /**
             * Se verifica si el usuario envío el form (submit) y si además
             * dentro del array POST existe uno llamado "Socios"
             * el cual aplica la autocarga de objeto para guardar los
             * datos enviado por POST utilizando autocarga de objeto
             */
            if (Input::hasPost('Socios')) {
                /**
                 * se le pasa al modelo por constructor los datos del form y ActiveRecord recoge esos datos
                 * y los asocia al campo correspondiente siempre y cuando se utilice la convención
                 * model.campo
                 */
                $Socio = new Socio(Input::post('Socios'));

                // En caso que falle la operación de guardar
                if ($Socio->create()) {
                    Flash::valid('Operación exitosa');
                    // Eliminamos el POST, si no queremos que se vean en el form
                    Input::delete();
                } else {
                    throw new Exception('Falló Operación');
                }
            }
        } catch (Exception $e) {
            Flash::error($e->getMessage());
            return Redirect::to();
        }
    }


    /**
     * Edita un Registro
     *
     * @param int $id (requerido)
     */
    public function modif($id)
    {
        try {
            $Socio = new Socio();

            //lista de categorías
            $this->categorias = ['Musculacion', 'Aerobicos', 'Box', 'Crossfit', 'Functional'];

            //Aplicando la autocarga de objeto, para comenzar la edición
            //Si en la vista usamos Socios.nombre, la autocarga buscará una variable llamada $Socios
            //para apoyar los helpers de Form
            $this->Socios = $Socio->find_by_id((int) $id);

            //se verifica si se ha enviado el formulario (submit)
            if (Input::hasPost('Socios')) {

                if ($Socio->update(Input::post('Socios'))) {
                    Flash::valid('Operación exitosa');
                    //enrutando por defecto al index del controller
                    //return Redirect::to();
                } else {
                    throw new Exception('Falló Operación');
                }
            }
        } catch (Exception $e) {
            Flash::error($e->getMessage());
            return Redirect::to();
        }
    }


    /**
     * Eliminar un Socio
     *
     * @param int $id (requerido)
     */
    public function baja($id)
    {
        try {
            if ((new Socio)->delete((int) $id)) {
                Flash::valid('Operación exitosa');
            } else {
                throw new Exception('Falló Operación');
            }

            //enrutando por defecto al index del controller
            return Redirect::to();
        } catch (Exception $e) {
            Flash::error($e->getMessage());
            return Redirect::to();
        }
    }
}
