<?php
class AsistenciaController extends AppController
{
    public $limit_params = FALSE;
    /**
     * Obtiene una lista para paginar los menús
     *
     * @param int $page [opcional]
     */
    public function index($page = 1)
    {
        $this->listTurnoAsistencia = (new TurnoAsistencia)->getAsistenciasTodas();
    }

    //recuperar la inscripción de un socio por su id
    public function getAsistenciaSocio($id)
    {
        $this->listTurnoAsistencia = (new TurnoAsistencia)->getAsistenciasSocio($id);
        $this->id = $id;
    }
    
    /**
     * Crea un Registro
     */
    public function consultarAsistencia($page = 1)
    {
        /**
         * Se verifica si el usuario envío el form (submit) y si además
         * dentro del array POST existe uno llamado "Clases"
         * el cual aplica la autocarga de objeto para guardar los
         * datos enviado por POST utilizando autocarga de objeto
         */
        $this->listTurno = (new Turno)->getTurnos($page);
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
            //1er Paso, seleccionar Categoria
            $this->categoria_seleccionada = Input::post('Turnos.categoria');
        } elseif (Input::hasPost('seleccionar_clase_fecha') || Input::hasPost('agregar')) {
            //2do Paso, seleccionar Clase y Fecha
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
                //$this->socios[$v->id] = $v->dni . ' ' ;
            }

            //TODO: verificar que la fecha sea válida
            $this->clase_seleccionada = Input::post('Turnos.id_clase');
            $this->fecha_seleccionada = Input::post('Turnos.fecha');
            $this->socio_seleccionado = Input::post('Turnos.id_socio');
            $this->horariosOcupados = (new Turno)->getTurnosTodos($this->clase_seleccionada, $this->fecha_seleccionada, $this->socio_seleccionado);

            if (Input::hasPost('agregar')) {
                // Verificar si el socio ya tiene un turno en la misma fecha y horario
                //$duracion = Input::post('Turnos.duracion');
                $hti = strtotime('2022-1-1 ' . Input::post('Turnos.HorarioInicio'));
                $htf = strtotime('2022-1-1 ' . Input::post('Turnos.HorarioInicio') . ' + 1 hour');


                $ocupado = false;

                // Recorro los turnos ya guardados para esta fecha/clase
                foreach ($this->horariosOcupados as $h) {
                    // Verificar si el turno se superpone con otro turno
                    $hoi = strtotime('2022-1-1 ' . $h->HorarioInicio);
                    $hof = strtotime('2022-1-1 ' . $h->HorarioFin);

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
            }
        }
    }

    public function verAsistencia($nro_turno)
    {

        $this->listTurnoAsistencia = (new TurnoAsistencia())->getTurnosAsistencia($nro_turno);
        $this->listaTurnos = (new Turno())->getTurnosTodos();
        $this->nro_turno = $nro_turno;
        $this->totalAsistencia = 0;

        foreach ($this->listTurnoAsistencia as $turnoAsistencia) {
            $this->totalAsistencia += $turnoAsistencia->precio;
        }
    }

    //genera una nueva asistencia
    public function nuevaAsistencia($id)
    {
        $this->nro_turno = (new TurnoAsistencia)->nuevaAsistencia($id);
        if ($this->nro_turno === false) {
            Flash::error('Falló Operación');
        } else {
            $this->listTurnoAsistencia = new TurnoAsistencia($this->nro_turno);
            Flash::valid('Operación exitosa');
        }
    }

    //Agrega una clase a una inscripcion
    public function agregarTurno($nro_turno)
    {

        if (Input::hasPost('turnos')) {
            $nuevo_elemnto_turno = new DetalleTurnoAsistencia(); //creo un elemento de detalle orden

            $elementoAgregado = Input::post('turnos'); //recupero el elelemnto agregado

            $unTurno = (new Turno())->find_first("conditions: nro_turno= " . $nro_turno); //recupero de la base de datos el id de la inscripcion por su nro_turno

            $nuevo_elemnto_turno->agregaTurno($unTurno->id, $elementoAgregado);
        }

        Redirect::to("asistencia/verAsistencia/" . $nro_turno);
    }
}
