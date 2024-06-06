<?php
class InstructorController extends AppController
{

    /**
     * Obtiene una lista para paginar los menús
     *
     * @param int $page [opcional]
     */
    public function index($page = 1)
    {
        $this->listInstructor = (new Instructor)->getInstructores($page);
    }

    /**
     * Crea un Registro
     */
    public function alta()
    {
        try {
            /**
             * Se verifica si el usuario envío el form (submit) y si además
             * dentro del array POST existe uno llamado "Instructores"
             * el cual aplica la autocarga de objeto para guardar los
             * datos enviado por POST utilizando autocarga de objeto
             */
            if (Input::hasPost('Instructores')) {
                /**
                 * se le pasa al modelo por constructor los datos del form y ActiveRecord recoge esos datos
                 * y los asocia al campo correspondiente siempre y cuando se utilice la convención
                 * model.campo
                 */
                $Instructor = new Instructor(Input::post('Instructores'));
                // En caso que falle la operación de guardar
                if ($Instructor->create()) {
                    Flash::valid('Operación exitosa');
                    // Eliminamos el POST, si no queremos que se vean en el form
                    Input::delete();
                    return;
                }

                throw new Exception('Falló Operación');
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
            $Instructor = new Instructor();

            // se verifica si se ha enviado el formulario (submit)
            if (Input::hasPost('Instructores')) {

                if ($Instructor->update(Input::post('Instructores'))) {
                    Flash::valid('Operación exitosa');
                    // enrutando por defecto al index del controller
                    return Redirect::to();
                }

                throw new Exception('Falló Operación');
            }

            // Aplicando la autocarga de objeto, para comenzar la edición
            // Si en la vista usamos Instructors.nombre, la autocarga buscará una variable llamada $Instructors
            // para apoyar los helpers de Form
            $this->Instructores = $Instructor->find_by_id((int) $id);
        } catch (Exception $e) {
            Flash::error($e->getMessage());
            return Redirect::to();
        }
    }

    /**
     * Eliminar un Instructor
     *
     * @param int $id (requerido)
     */
    public function baja($id)
    {
        try {
            if ((new Instructor)->delete((int) $id)) {
                Flash::valid('Operación exitosa');
            } else {
                throw new Exception('Falló Operación');
            }

            // enrutando por defecto al index del controller
            return Redirect::to();
        } catch (Exception $e) {
            Flash::error($e->getMessage());
            return Redirect::to();
        }
    }
}
