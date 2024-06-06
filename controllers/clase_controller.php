<?php
class ClaseController extends AppController
{

    /**
     * Obtiene una lista para paginar los menús
     *
     * @param int $page [opcional]
     */
    public function index($page = 1)
    {
        // Obtén todas las clases (puede ser que ya tengas esto)
        $this->listClase = (new Clase)->getClases($page);

        // Obtén las clases disponibles utilizando el método del modelo
        $claseModel = new Clase();
        $clasesDisponibles = $claseModel->getClasesDisponibles();

        // Pasa las clases disponibles a la vista de clase
        $this->clasesDisponibles = $clasesDisponibles;
        $this->nombreclase = ['Musculacion', 'Aerobicos', 'Box', 'Crossfit', 'Functional'];
    }
    private function validarHorarios($horarioinicio, $horariofin)
    {
        // Convierte los horarios a objetos DateTime para facilitar la comparación
        $inicio = DateTime::createFromFormat('H:i', $horarioinicio);
        $fin = DateTime::createFromFormat('H:i', $horariofin);

        // Define el rango de horarios permitidos
        $horaInicioPermitida = DateTime::createFromFormat('H:i', '07:00');
        $horaFinPermitida = DateTime::createFromFormat('H:i', '21:00');

        // Verifica si los horarios están dentro del rango permitido
        if ($inicio >= $horaInicioPermitida && $fin <= $horaFinPermitida) {
            // Verifica que haya un intervalo de 1 hora entre inicio y fin
            $intervalo = $inicio->diff($fin);
            if ($intervalo->h == 1 && $intervalo->i == 0) {
                return true; // Los horarios son válidos
            }
        }

        return false; // Los horarios no son válidos
    }
    /**
     * Crea un Registro
     */
    public function alta()
    {
        try {
            if (Input::hasPost('Clases')) {
                $datosClase = Input::post('Clases');
    
                // Realiza validaciones de los campos, por ejemplo:
                if (empty($datosClase['nombre'])) {
                    throw new Exception('El campo Nombre es obligatorio.');
                } elseif (empty($datosClase['instructor'])) {
                    throw new Exception('El campo Instructor es obligatorio.');
                } elseif (empty($datosClase['dias'])) {
                    throw new Exception('Debe seleccionar al menos un día.');
                } elseif (empty($datosClase['HorarioInicio']) || empty($datosClase['HorarioFin'])) {
                    throw new Exception('Debe seleccionar ambos horarios de inicio y fin.');
                } elseif (!$this->validarHorarios($datosClase['HorarioInicio'], $datosClase['HorarioFin'])) {
                    throw new Exception('Los horarios no son válidos. Deben ser intervalos de 1 hora entre 07:00 a 21:00.');
                } else {
                    $clase = new Clase($datosClase);
                    if ($clase->create()) {
                        Flash::valid('Operación exitosa');
                        Input::delete();
                        return Redirect::to(); // Redirige a la lista de clases o a donde desees
                    }
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
            $Clase = new Clase();

            // Verificamos si se ha enviado el formulario (submit)
            if (Input::hasPost('Clases')) {
                $datos = Input::post('Clases');

                // Realiza validaciones de los campos, por ejemplo:
                if (empty($datos['nombre'])) {
                    throw new Exception('El campo Nombre es obligatorio.');
                } elseif (empty($datos['instructor'])) {
                    throw new Exception('El campo Instructor es obligatorio.');
                } elseif (empty($datos['dias'])) {
                    throw new Exception('Debe seleccionar al menos un día.');
                } elseif (empty($datos['HorarioInicio']) && empty($datos['HorarioFin'])) {
                    throw new Exception('Debe seleccionar al menos un horario.');
                } elseif (!$this->validarHorarios($datos['HorarioInicio'], $datos['HorarioFin'])) {
                    throw new Exception('Los horarios no son válidos. Deben ser intervalos de 1 hora entre 07:00 a 21:00. O vuelva a ingresar los horarios.');
                } else {
                    // Todas las validaciones pasaron, intentamos actualizar los datos
                    if ($Clase->update($datos)) {
                        Flash::valid('Operación exitosa');
                        // Redirigimos al index del controlador
                        return Redirect::to();
                    } else {
                        throw new Exception('Falló la actualización en la base de datos.');
                    }
                }
            }
            // Aplicando la autocarga de objeto para cargar los datos de la clase a editar
            // Si en la vista usamos Clases.nombre, la autocarga buscará una variable llamada $Clases
            // para apoyar los helpers de Form
            $this->Clases = $Clase->find_by_id((int) $id);

        } catch (Exception $e) {
            Flash::error($e->getMessage());
            
            return Redirect::to();
        }
    }


    /**
     * Eliminar un Clase
     *
     * @param int $id (requerido)
     */
    public function baja($id)
    {
        try {
            if ((new Clase)->delete((int) $id)) {
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
