<?PHP

    require_once "AutentificadorJWT.php";

    class Usuario
    {
    //--------------------------------------------------------------------------------//
    //--ATRIBUTOS
        private $_nombre;
        private $_pass;
        private $_estado;
    //--------------------------------------------------------------------------------//

    //--------------------------------------------------------------------------------//
    //--GETTERS
        public function GetNombre()
        {
            return $this->_nombre;
        }

        public function GetPass()
        {
            return $this->_pass;
        }

        public function GetTurno()
        {
            return $this->_estado;
        }
    //--------------------------------------------------------------------------------//
    //--CONSTRUCTOR
        public function __construct($nombre, $pass, $estado)
        {
            if ($nombre !== NULL && $pass !== NULL && $estado !== NULL)
            {
                $this->_nombre = $nombre;
                $this->_pass = $pass; 
                $this->_estado = $estado;
            } 
        }
    //--------------------------------------------------------------------------------//
    //--TO STRING
        public function ToString()
        {
            $str = parent::ToString() . " - ";

            $str .= $this->_nombre . " - ";
            $str .= $this->_pass . " - ";
            $str .= $this->_estado;

            return $str;
        }
    //--------------------------------------------------------------------------------//

    //--------------------------------------------------------------------------------//
    //--METODOS DE INSTANCIA

    //--------------------------------------------------------------------------------//
    //--METODOS DE CLASE
        public static function AgregarUsuarioBD($id_emp, $dni)
        {
            $id = 0;
            $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

            $consulta =$objetoAccesoDato->RetornarConsulta("INSERT INTO usuarios (id_emp, usuario, pass, perfil, estado) 
                VALUES(:id_emp, :usuario, :pass, :perfil, :estado)");

            $consulta->bindValue(':id_emp', $id_emp, PDO::PARAM_INT);
            $consulta->bindValue(':usuario', $dni, PDO::PARAM_STR);
            $consulta->bindValue(':pass', $dni, PDO::PARAM_STR);
            $consulta->bindValue(':perfil', "empleado", PDO::PARAM_STR);
            $consulta->bindValue(':estado', "habilitado", PDO::PARAM_STR);
            $consulta->execute();
            $id = $objetoAccesoDato->ObtenerUltimoId();

            return $id;
        }

        public static function EliminarUsuarioBD($id)
        {
            $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

            $consulta = $objetoAccesoDato->RetornarConsulta("DELETE FROM usuarios WHERE id_usuario = :id");

            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
        } 

        public static function ValidarUsuario($usuario, $pass)
        {
            $fecha = date("Y-m-d H:i:s");
            $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

            $consulta = $objetoAccesoDato->RetornarConsulta("SELECT usuario, id_emp, perfil FROM usuarios 
                WHERE usuario = :usuario AND pass = :pass AND estado != 'bloqueado'");
            
            $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR);
            $consulta->bindValue(':pass', $pass, PDO::PARAM_STR);
            $consulta->execute();
            $consulta->setFetchMode(PDO::FETCH_ASSOC);
            if ($datos = $consulta->fetch()) 
            {
                $token = AutentificadorJWT::CrearToken($datos);
                self::RegistrarLogin($datos["id_emp"], $fecha); 
                $datos["token"] = $token;  
            }

            return $datos;
        }

        private static function RegistrarLogin($id_emp, $fecha)
        {
            $id = 0;
            $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

            $consulta = $objetoAccesoDato->RetornarConsulta("INSERT INTO logins (id_usuario, fecha_login) 
                VALUES((SELECT id_usuario FROM usuarios WHERE id_emp = :id_emp), :fecha)");
            
            $consulta->bindValue(':id_emp', $id_emp, PDO::PARAM_INT);
            $consulta->bindValue(':fecha', $fecha, PDO::PARAM_STR);
            $consulta->execute();
            $id = $objetoAccesoDato->ObtenerUltimoId();

            return $id;
        }

        public static function ObtenerLogins($id_emp, $desde, $hasta)
        {
            $logins = array();
            $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

            $consulta = $objetoAccesoDato->RetornarConsulta("SELECT fecha_login FROM logins
                WHERE (id_usuario = (SELECT id_usuario FROM usuarios WHERE id_emp = :id_emp)) 
                AND fecha_login BETWEEN :desde AND :hasta");
            
            $consulta->bindValue(':id_emp', $id_emp, PDO::PARAM_INT);
            $consulta->bindValue(':desde', $desde, PDO::PARAM_STR);
            $consulta->bindValue(':hasta', $hasta, PDO::PARAM_STR);
            $consulta->execute();
            $consulta->setFetchMode(PDO::FETCH_ASSOC);
            while ($datos = $consulta->fetch())
                $logins[] = $datos;

            return $logins;
        }

        public static function ModificarContraseña($usuario, $pass, $newPass)
        {
            $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

            $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE usuarios SET pass = :newPass
                WHERE usuario = :usuario AND pass = :pass");
            
            $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR);
            $consulta->bindValue(':pass', $pass, PDO::PARAM_STR);
            $consulta->bindValue(':newPass', $newPass, PDO::PARAM_STR);
            $consulta->execute();
        }

        public static function ModificarEstado($id_emp, $estado)
        {
            $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

            $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE usuarios SET estado = :estado
                WHERE id_emp = :id_emp");
            
            $consulta->bindValue(':id_emp', $id_emp, PDO::PARAM_INT);
            $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
            $consulta->execute();
        }
    }
?>