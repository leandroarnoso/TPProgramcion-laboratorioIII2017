<?PHP
    require_once "Vehiculo.php";

    class Auto extends Vehiculo
    {
    //--------------------------------------------------------------------------------//
    //--ATRIBUTOS

    //--------------------------------------------------------------------------------//

    //--------------------------------------------------------------------------------//
    //--GETTERS
    
    //--------------------------------------------------------------------------------//
    //--CONSTRUCTOR
        public function __construct($patente, $marca, $color)
        {
            parent::__construct($patente, $marca, $color);
        }
    //--------------------------------------------------------------------------------//
    //--TO STRING
        public function ToString()
        {
            $str = parent::ToString();

            return $str;
        }
    //--------------------------------------------------------------------------------//

    //--------------------------------------------------------------------------------//
    //--METODOS DE INSTANCIA
        public function AgregarAutoBD()
        {
            $id = 0;
            $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

            $consulta =$objetoAccesoDato->RetornarConsulta("INSERT INTO automoviles (patente, marca, color)
                VALUES(:patente, :marca, :color)");

            $consulta->bindValue(':patente', $this->_patente, PDO::PARAM_STR);
            $consulta->bindValue(':marca', $this->_marca, PDO::PARAM_STR);
            $consulta->bindValue(':color', $this->_color, PDO::PARAM_STR);
            $consulta->execute();
            $id = $objetoAccesoDato->ObtenerUltimoId();

            return $id;
        }
    //--------------------------------------------------------------------------------//
    //--METODOS DE CLASE
        public static function ObtenerAutosBD($instanciar = false)
        {
            $arrayAutos = array();
            $auto = NULL;
            $objetoAcceso = AccesoDatos::DameUnObjetoAcceso(); 
            
            $consulta = $objetoAcceso->RetornarConsulta("SELECT * from automoviles");
            $consulta->execute();
            $consulta->setFetchMode(PDO::FETCH_ASSOC);
            while ($datos = $consulta->fetch())
            {
                if ($instanciar)
                {
                    $auto = new Auto($datos["patente"], $datos["marca"], $datos["color"]);
                    $arrayAutos[$datos["id_auto"]] = $auto;
                }
                else
                    $arrayAutos[] = $datos;
            }

            return $arrayAutos;
        }

        public static function EliminarAutoBD($id)
        {
            $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

            $consulta = $objetoAccesoDato->RetornarConsulta("DELETE FROM automoviles WHERE id_auto = :id");

            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
        } 

        public static function RegistrosDeAutos($desde, $hasta)
        {
            $registrosDeAuto = array();
            $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

            $consulta = $objetoAccesoDato->RetornarConsulta("SELECT automoviles.*, cocheras.*, fecha_ingreso, 
                fecha_salida, monto FROM registros LEFT JOIN automoviles ON registros.id_auto = automoviles.id_auto 
                LEFT JOIN cocheras ON registros.id_cochera = cocheras.id_cochera 
                WHERE fecha_ingreso BETWEEN :desde AND :hasta ORDER BY automoviles.patente");

            $consulta->bindValue(':desde', $desde, PDO::PARAM_STR);
            $consulta->bindValue(':hasta', $hasta, PDO::PARAM_STR);
            $consulta->execute();
            $consulta->setFetchMode(PDO::FETCH_ASSOC);
            while($datos = $consulta->fetch())
                $registrosDeAuto[] = $datos;
                
            return $registrosDeAuto;
        }

        public static function ObtenerAuto($col, $dato)
        {
            $auto = array();
            $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

            $consulta = $objetoAccesoDato->RetornarConsulta("SELECT * FROM automoviles WHERE " . $col . " = :dato");

            $consulta->bindValue(':dato', $dato);
            $consulta->execute();
            $consulta->setFetchMode(PDO::FETCH_ASSOC);
            $auto = $consulta->fetch();

            return $auto;
        }

    }
?>