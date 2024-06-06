<?php
class TurnoController extends AppController
{

    /**
     * Obtiene una lista para paginar los menús
     *
     * @param int $page [opcional]
     */
    public function index($page = 1)
    {
        $this->listTurno = (new Turno)->getTurnos($page);
    }

    public function getTurnoSocio($id)
    {
        $this->listTurno = (new Turno)->getTurnosSocio($id);
        $this->id = $id;
    }

    /**
     * Crea un Registro
     */
    public function alta()
    {
        /**
         * Se verifica si el usuario envío el form (submit) y si además
         * dentro del array POST existe uno llamado "Clases"
         * el cual aplica la autocarga de objeto para guardar los
         * datos enviado por POST utilizando autocarga de objeto
         */

        $this->nombreclase = ['Musculacion', 'Aerobicos', 'Box', 'Crossfit', 'Functional'];

        //Clases
        $clases = (new Clase())->getClasesTodas();
        $this->clases = [];
        foreach ($clases as $v) {
            $this->clases[$v->id] = $v->id . ' ' . $v->nombre;
        }
        // select para elegir Socios
        $this->socios = [];
        $socios = (new Socio)->getSociosTodos();
        foreach ($socios as $socio) {
            $selectorSocios[$socio->id] = $socio->nombre . ' ' . $socio->apellido;
        }
        $this->selectorSocios = $selectorSocios;
        //Consigna 4. Registro de asistencias de socios.
        if (Input::hasPost('seleccionar_categoria')) {
            // select Categoria
            $this->categoria_seleccionada = Input::post('Turnos.categoria');
        } elseif (Input::hasPost('seleccionar_clase_fecha') || Input::hasPost('agregar')) {
            //seleccionar Clase y Fecha
            $this->categoria_seleccionada = Input::post('Turnos.categoria');

            if (Input::hasPost('seleccionar_clase_fecha') || Input::hasPost('agregar')) {
                // ...PARTE NUEVA PARA EL SOCIOS
                $this->fecha_seleccionada = Input::post('Turnos.fecha');
                $this->clase_seleccionada = Input::post('Turnos.id_clase');
                $this->socio_seleccionado = Input::post('Turnos.id_socio'); // Agregar la selección del socio
                $this->horariosOcupados = (new Turno)->getTurnosPorSocio($this->clase_seleccionada, $this->fecha_seleccionada, $this->socio_seleccionado);
                // ...
            }

            $this->socios = [];
            $socios = (new Socio)->getSociosTodos(
                ($this->categoria_seleccionada == 'socio')
            );
            foreach ($socios as $v) {
                $this->socios[$v->id] = $v->apellido . ', ' . $v->nombre;
                $this->socios[$v->id] = $v->dni . ' ';
            }

            if (Input::hasPost('agregar')) {
                $this->horariosOcupados = (new Turno)->getTurnosFiltrados($this->clase_seleccionada, $this->fecha_seleccionada, $this->socio_seleccionado);

                // Verificar si el socio ya tiene un turno en la misma fecha y horario
                $hti = strtotime('2023-1-1 ' . Input::post('Turnos.HorarioInicio'));
                $htf = strtotime('2023-1-1 ' . Input::post('Turnos.HorarioInicio') . ' + 1 hour');

                $ocupado = false;

                // Recorro los turnos ya guardados para esta fecha/clase
                foreach ($this->horariosOcupados as $h) {
                    // Verificar si el turno se superpone con otro turno
                    $hoi = strtotime('2023-1-1 ' . $h->HorarioInicio);
                    $hof = strtotime('2023-1-1 ' . $h->HorarioFin);

                    if (($hti >= $hoi && $hti < $hof) || ($htf > $hoi && $htf <= $hof)) {
                        $ocupado = true;
                        break;
                    }
                }

                // Validar si el socio ya tiene un turno en la misma fecha y horario
                if ($ocupado) {
                    Flash::error('El horario seleccionado ya está reservado.');
                } else {
                    // Continuar con el proceso de guardar el turno
                    $datos = Input::post('Turnos');
                    $datos['HorarioFin'] = date('H:i:s', $htf);

                    $Turno = new Turno($datos);

                    // En caso de que falle la operación de guardar
                    if ($Turno->create()) {
                        Flash::valid('Su asistencia ha sido registrada');
                        Input::delete(); // Eliminamos el POST si no queremos que se vean en el formulario
                    } else {
                        Flash::error('Falló la operación');
                    }
                }
            } else {
                // Si el usuario no ha hecho clic en 'agregar', obtén todos los turnos
                $this->horariosOcupados = (new Turno)->getTurnosTodos();
            }
        }
    }


    /**
     * Edita un Registro
     *
     * @param int $id (requerido)
     */
    public function modif($id)
    {
        $Turno = new Turno();

        //se verifica si se ha enviado el formulario (submit)
        if (Input::hasPost('Turnos')) {

            if ($Turno->update(Input::post('Turnos'))) {
                Flash::valid('Operación exitosa');
                //enrutando por defecto al index del controller
                return Redirect::to();
            }
            Flash::error('Falló Operación');
            return;
        }


        //Aplicando la autocarga de objeto, para comenzar la edición
        //para apoyar los helpers de Form
        $this->Turnos = $Turno->find_by_id((int) $id);
    }

    /**
     * Eliminar un Turno
     *
     * @param int $id (requerido)
     */
    public function baja($id)
    {
        if ((new Turno)->delete((int) $id)) {
            Flash::valid('Operación exitosa');
        } else {
            Flash::error('Falló Operación');
        }

        //enrutando por defecto al index del controller
        return Redirect::to();
    }

    /**
     * Crea un Registro
     */
    public function consultarAsistencia($page = 1)
    {
        $this->nombreclase = ['Musculacion', 'Aerobicos', 'Box', 'Crossfit', 'Functional'];

        //Clases
        $clases = (new Clase())->getClasesTodas();
        $this->clases = [];
        foreach ($clases as $v) {
            $this->clases[$v->id] = $v->id . ' ' . $v->nombre;
        }

        // select para elegir Socios
        $this->socios = [];
        $socios = (new Socio)->getSociosTodos();
        foreach ($socios as $socio) {
            $selectorSocios[$socio->id] = $socio->nombre . ' ' . $socio->apellido;
        }
        $this->selectorSocios = $selectorSocios;

        if (Input::hasPost('seleccionar_clase_fecha')) {
            //2do Paso, seleccionar Clase y Fecha
            $this->categoria_seleccionada = Input::post('Turnos.categoria');
            $this->clase_seleccionada = Input::post('Turnos.id_clase');
            $this->socio_seleccionado = Input::post('Turnos.id_socio'); // Agregar la selección del socio

            // Get the attendances for the selected date
            $this->listTurno = (new Turno)->getTurnosPorClase($this->clase_seleccionada, $this->socio_seleccionado);
        }
    }

    // public function verAsistencia($nro_turno)
    // {


    //     $this->listTurno = (new Turno())->getAsistenciasTurno($nro_turno);
    //     $this->listaTurnos = (new Turno())->getTurnosTodos();
    //     $this->nro_turno = $nro_turno;
    //     $this->totalInscripcion = 0;

    //     foreach ($this->listTurno as $turno) {
    //         $this->totalInscripcion += $turno->precio;
    //     }
    // }

    public function agregarTurno($nro_turno)
    {
        if (Input::hasPost('turnos')) {
            $nuevo_elemento_turno = new DetalleTurnoAsistencia();

            $elementoAgregado = Input::post('turnos');

            $unTurno = (new Turno())->find_first("conditions: nro_turno= " . $nro_turno);

            // Pasa el id_turno_asistencia y el id_clase al método agregaTurno
            $nuevo_elemento_turno->agregaTurno($unTurno->id, $elementoAgregado);
        }

        Redirect::to("turno/verAsistencia/" . $nro_turno);
    }
}
