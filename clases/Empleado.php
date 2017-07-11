<?PHP
    require_once "Persona.php";
    require_once "Usuario.php";

    class Empleado extends Persona
    {
    //--------------------------------------------------------------------------------//
    //--ATRIBUTOS
        private $_legajo;
        private $_sueldo;
        private $_turno;
    //--------------------------------------------------------------------------------//

    //--------------------------------------------------------------------------------//
    //--GETTERS
        public function GetLegajo()
        {
            return $this->_legajo;
        }

        public function GetSueldo()
        {
            return $this->_sueldo;
        }

        public function GetTurno()
        {
            return $this->_turno;
        }
    //--------------------------------------------------------------------------------//
    //--CONSTRUCTOR
        public function __construct($dni, $apellido, $nombre, $sexo, $legajo, $sueldo, $turno)
        {
            if ($legajo !== NULL && $sueldo !== NULL && $turno !== NULL)
            {
                parent::__construct($dni, $apellido, $nombre, $sexo);
                $this->_legajo = $legajo;
                $this->_sueldo = $sueldo; 
                $this->_turno = ucfirst(strtolower($turno));
            } 
        }
    //--------------------------------------------------------------------------------//
    //--TO STRING
        public function ToString()
        {
            $str = parent::ToString() . " - ";

            $str .= $this->_legajo . " - ";
            $str .= $this->_sueldo . " - ";
            $str .= $this->_idTurno;

            return $str;
        }
    //--------------------------------------------------------------------------------//

    //--------------------------------------------------------------------------------//
    //--METODOS DE INSTANCIA
        public function AgregarEmpleadoBD()
        {
            $id = 0;
            $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

            $consulta =$objetoAccesoDato->RetornarConsulta("INSERT INTO empleados (dni, apellido, nombre, sexo, legajo, sueldo, id_turno, suspendido) 
                VALUES(:dni, :apellido, :nombre, :sexo, :legajo, :sueldo, (SELECT turnos.id_turno FROM turnos WHERE turnos.turno = :turno), 'no')");

            $consulta->bindValue(':dni', $this->_dni, PDO::PARAM_INT);
            $consulta->bindValue(':apellido', $this->_apellido, PDO::PARAM_STR);
            $consulta->bindValue(':nombre', $this->_nombre, PDO::PARAM_STR);
            $consulta->bindValue(':sexo', $this->_sexo, PDO::PARAM_STR);
            $consulta->bindValue(':legajo', $this->_legajo, PDO::PARAM_INT);
            $consulta->bindValue(':sueldo', $this->_sueldo, PDO::PARAM_STR);
            $consulta->bindValue(':turno', $this->_turno, PDO::PARAM_STR);
            $consulta->execute();
            if ($id = $objetoAccesoDato->ObtenerUltimoId())
                Usuario::AgregarUsuarioBD($id, $this->_dni);

            return $id;
        }

    //--------------------------------------------------------------------------------//
    //--METODOS DE CLASE
        public static function ObtenerEmpleadosBD($instanciar = false)
        {
            $arrayEmpleados = array();
            $empleado = NULL;
            $objetoAcceso = AccesoDatos::DameUnObjetoAcceso(); 
            
            $consulta = $objetoAcceso->RetornarConsulta("SELECT empleados.id_emp, empleados.dni, empleados.apellido, 
                empleados.nombre, empleados.sexo, empleados.legajo, empleados.sueldo, turnos.turno, empleados.suspendido
                FROM empleados LEFT JOIN turnos ON empleados.id_turno = turnos.id_turno
                WHERE empleados.id_emp NOT IN (SELECT empleados_despedidos.id_emp FROM empleados_despedidos)");
            $consulta->execute();
            $consulta->setFetchMode(PDO::FETCH_ASSOC);
            while ($datos = $consulta->fetch())
            {
                if ($instanciar)
                {
                    $empleado = new Empleado($datos["dni"], $datos["apellido"], $datos["nombre"], $datos["sexo"], 
                        $datos["legajo"], $datos["sueldo"], $datos["turno"]);
                    $arrayEmpleados[$datos["id_emp"]] = $empleado;
                }
                else
                    $arrayEmpleados[] = $datos;
            }

            return $arrayEmpleados;
        }

        /*public static function EliminarEmpleadoBD($id)
        {
            $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

            $consulta = $objetoAccesoDato->RetornarConsulta("DELETE FROM empleados WHERE id_emp = :id");

            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
        }*/

        public static function DarDeBajaEmpleadoBD($id_emp, $descripcion)
        {
            $id = 0;
            $fecha = date("Y-m-d H:i:s");
            $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

            $consulta = $objetoAccesoDato->RetornarConsulta("INSERT INTO empleados_despedidos (id_emp, fecha_despido, descripcion)
                VALUES(:id_emp, :fecha, :descripcion)");

            $consulta->bindValue(':id_emp', $id_emp, PDO::PARAM_INT);
            $consulta->bindValue(':fecha', $fecha, PDO::PARAM_STR);
            $consulta->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
            $consulta->execute();
            if ($id = $objetoAccesoDato->ObtenerUltimoId())
                Usuario::ModificarEstado($id_emp, "bloqueado");

            return $id;
        }

        public static function SuspenderEmpleadoBD($id_emp, $descripcion)
        {
            $id = 0;

            if ($suspender = self::EsPosibleSuspender($id_emp))
            {
                $fecha = date("Y-m-d H:i:s");
                $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

                $consulta = $objetoAccesoDato->RetornarConsulta("INSERT INTO empleados_suspendidos (id_emp, fecha_suspension, descripcion)
                    VALUES(:id_emp, :fecha, :descripcion)");

                $consulta->bindValue(':id_emp', $id_emp, PDO::PARAM_INT);
                $consulta->bindValue(':fecha', $fecha, PDO::PARAM_STR);
                $consulta->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
                $consulta->execute();
                if ($id = $objetoAccesoDato->ObtenerUltimoId())
                {
                    Self::ModificarEstado($id_emp, "si");
                    Usuario::ModificarEstado($id_emp, "bloqueado");
                }
            }

            return $id;
        }

        public static function ReintegrarEmpleadoBD($id_emp)
        {
            $retorno = false;
            $fecha = date("Y-m-d H:i:s");
            $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

            if (!$suspender = self::EsPosibleSuspender($id_emp))
            {
                $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE empleados_suspendidos SET fecha_reintegracion = :fecha
                    WHERE id_emp = :id_emp AND fecha_reintegracion IS NULL");

                $consulta->bindValue(':id_emp', $id_emp, PDO::PARAM_INT);
                $consulta->bindValue(':fecha', $fecha, PDO::PARAM_STR);
                $consulta->execute();
                Self::ModificarEstado($id_emp, "no");
                Usuario::ModificarEstado($id_emp, "habilitado");
                $retorno = true;
            }

            return $retorno;
        }

        public static function ObtenerEmpleado($col, $dato)
        {
            $empleado = array();
            $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

            $consulta = $objetoAccesoDato->RetornarConsulta("SELECT empleados.id_emp, empleados.dni, empleados.apellido, 
                empleados.nombre, empleados.sexo, empleados.legajo, empleados.sueldo, turnos.turno, empleados.suspendido
                FROM empleados LEFT JOIN turnos ON empleados.id_turno = turnos.id_turno WHERE " . $col . " = :dato
                AND empleados.id_emp NOT IN (SELECT empleados_despedidos.id_emp FROM empleados_despedidos)");

            $consulta->bindValue(':dato', $dato);
            $consulta->execute();
            $consulta->setFetchMode(PDO::FETCH_ASSOC);
            $empleado = $consulta->fetch();

            return $empleado;
        }

        public static function ObtenerOperacionesIngresos($id, $desde, $hasta)
        {
            $op = 0;
            $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

            $consulta = $objetoAccesoDato->RetornarConsulta("SELECT COUNT(id_emp_ingreso) as ingresos FROM registros 
                WHERE id_emp_ingreso = :id AND fecha_ingreso BETWEEN :desde AND :hasta");

            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->bindValue(':desde', $desde, PDO::PARAM_STR);
            $consulta->bindValue(':hasta', $hasta, PDO::PARAM_STR);
            $consulta->execute();
            $consulta->setFetchMode(PDO::FETCH_ASSOC);
            $op = $consulta->fetch();
                
            return $op;
        }

        public static function ObtenerOperacionesSalidas($id, $desde, $hasta)
        {
            $op = 0;
            $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

            $consulta = $objetoAccesoDato->RetornarConsulta("SELECT count(id_emp_egreso) as egresos FROM registros 
                WHERE id_emp_egreso = :id AND fecha_salida BETWEEN :desde AND :hasta");

            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->bindValue(':desde', $desde, PDO::PARAM_STR);
            $consulta->bindValue(':hasta', $hasta, PDO::PARAM_STR);
            $consulta->execute();
            $consulta->setFetchMode(PDO::FETCH_ASSOC);
            $op = $consulta->fetch();
                
            return $op;
        }

        private static function ModificarEstado($id_emp, $suspendido)
        {
            $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

            $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE empleados SET suspendido = :suspendido
                WHERE id_emp = :id_emp");
            
            $consulta->bindValue(':id_emp', $id_emp, PDO::PARAM_INT);
            $consulta->bindValue(':suspendido', $suspendido, PDO::PARAM_STR);
            $consulta->execute();
        }

        private static function EsPosibleSuspender($id_emp)
        {
            $retorno = false;
            $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

            $consulta = $objetoAccesoDato->RetornarConsulta("SELECT id_emp FROM empleados_suspendidos 
                WHERE id_emp = :id_emp AND fecha_reintegracion IS NULL");
            
            $consulta->bindValue(':id_emp', $id_emp, PDO::PARAM_INT);
            $consulta->execute();
            $consulta->setFetchMode(PDO::FETCH_ASSOC);
            if(!$id = $consulta->fetch())
                $retorno = true;

            return $retorno;
        }

    }
?>