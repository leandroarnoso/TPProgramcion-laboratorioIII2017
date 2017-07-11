<?PHP

    class Cochera
    {
    //--------------------------------------------------------------------------------//
    //--ATRIBUTOS
        private $_numero;
        private $_piso;
        private $_sector;
        private $_discapacitado;
    //--------------------------------------------------------------------------------//

    //--------------------------------------------------------------------------------//
    //--GETTERS
    public function GetNumero()
    {
        return $this->_numero;
    }

    public function GetPiso()
    {
        return $this->_piso;
    }

    public function GetSector()
    {
        return $this->_sector;
    }

    public function GetDiscapacitado()
    {
        return $this->_discapacitado;
    }
    //--------------------------------------------------------------------------------//
    //--CONSTRUCTOR
        public function __construct($numero, $piso, $sector, $discapacitado)
        {
            if ($numero !== NULL && $piso !== NULL && $sector !== NULL && $discapacitado !== NULL)
            {
                $this->_numero = $numero;
                $this->_piso = $piso;
                $this->_sector = $sector;
                $this->_discapacitado = $discapacitado;
            }
        }
    //--------------------------------------------------------------------------------//
    //--TO STRING
        public function ToString()
        {
            $str = "";

            $str .= "Numero: " . $this->_numero;
            $str .= " Piso: " . $this->_piso;
            $str .= " Sector: " . $this->_sector;
            $str .= " Discapacitado: " . $this->_discapacitado;

            return $str;
        }
    //--------------------------------------------------------------------------------//

    //--------------------------------------------------------------------------------//
    //--METODOS DE INSTANCIA
        public function AgregarCocheraBD()
        {
            $id = 0;
            $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

            $consulta =$objetoAccesoDato->RetornarConsulta("INSERT INTO cocheras (numero, piso, sector, discapacitado)" 
                . "VALUES(:numero, :piso, :sector, :discapacitado)");

            $consulta->bindValue(':numero', $this->_numero, PDO::PARAM_INT);
            $consulta->bindValue(':piso', $this->_piso, PDO::PARAM_INT);
            $consulta->bindValue(':sector', $this->_sector, PDO::PARAM_STR);
            $consulta->bindValue(':discapacitado', $this->_discapacitado, PDO::PARAM_STR);
            $consulta->execute();
            $id = $objetoAccesoDato->ObtenerUltimoId();

            return $id;
        }
    //--------------------------------------------------------------------------------//
    //--METODOS DE CLASE
        public static function ObtenerAutosCocherasBD($instanciar = false)
        {
            $arrayAutosCocheras = array();
            $objetoAcceso = AccesoDatos::DameUnObjetoAcceso(); 
            
            $consulta = $objetoAcceso->RetornarConsulta("SELECT automoviles.*, cocheras.* FROM registros LEFT JOIN automoviles 
                ON registros.id_auto = automoviles.id_auto LEFT JOIN cocheras ON registros.id_cochera = cocheras.id_cochera 
                WHERE registros.fecha_salida is NULL");

            $consulta->execute();
            $consulta->setFetchMode(PDO::FETCH_ASSOC);
            while ($datos = $consulta->fetch())
            {
                if ($instanciar)
                {
                    $auto = new Auto($datos["patente"], $datos["marca"], $datos["color"]);
                    $cochera = new Cochera($datos["numero"], $datos["piso"], $datos["sector"], $datos["discapacitado"]);
                    $arrayAutosCocheras[] = array($datos["id_auto"] => $auto, $datos["id_cochera"] => $cochera); 
                }
                else
                    $arrayAutosCocheras[] = $datos;
            }

            return $arrayAutosCocheras;
        }

        public static function ObtenerCocherasVaciasBD($instanciar = false)
        {
            $arrayCocherasVacias = array();
            $objetoAcceso = AccesoDatos::DameUnObjetoAcceso(); 
            
            $consulta = $objetoAcceso->RetornarConsulta("SELECT cocheras.* FROM cocheras WHERE cocheras.id_cochera NOT IN 
                (SELECT registros.id_cochera FROM registros WHERE fecha_salida IS NULL)");
            
            $consulta->execute();
            $consulta->setFetchMode(PDO::FETCH_ASSOC);
            while ($datos = $consulta->fetch())
            {
                if ($instanciar)
                {
                    $cochera = new Cochera($datos["numero"], $datos["piso"], $datos["sector"], $datos["discapacitado"]);
                    $arrayCocherasVacias[$datos["id_cochera"]] = $cochera; 
                }
                else
                    $arrayCocherasVacias[] = $datos;
            }

            return $arrayCocherasVacias;
        }
        
        public static function ObtenerCocherasBD($instanciar = false)
        {
            $arrayCocheras = array();
            $cochera = NULL;
            $objetoAcceso = AccesoDatos::DameUnObjetoAcceso(); 
            
            $consulta = $objetoAcceso->RetornarConsulta("SELECT * FROM cocheras 
                ORDER BY piso ASC, sector ASC, numero ASC");
            $consulta->execute();
            $consulta->setFetchMode(PDO::FETCH_ASSOC);
            while ($datos = $consulta->fetch())
            {
                if ($instanciar)
                {
                    $cochera = new Cochera($datos["numero"], $datos["piso"], $datos["sector"], $datos["discapacitado"]);
                    $arrayCocheras[$datos["id_cochera"]] = $cochera;
                }
                else
                    $arrayCocheras[] = $datos;
            }

            return $arrayCocheras;
        }

        public static function UsoDeCocheras($desde, $hasta, $str)
        {
            $cocheras = array();
            $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

            $consulta =$objetoAccesoDato->RetornarConsulta("SELECT cocheras.*, COUNT(registros.id_cochera) as usos 
            FROM cocheras LEFT JOIN registros ON cocheras.id_cochera = registros.id_cochera 
            AND fecha_ingreso BETWEEN :desde AND :hasta WHERE " . $str . " GROUP BY cocheras.id_cochera ASC");

            $consulta->bindValue(':desde', $desde, PDO::PARAM_STR);
            $consulta->bindValue(':hasta', $hasta, PDO::PARAM_STR);
            $consulta->execute();
            $consulta->setFetchMode(PDO::FETCH_ASSOC);
            while ($datos = $consulta->fetch())
                $cocheras[] = $datos;
                
            return $cocheras;
        }

        public static function EliminarCocherasBD($id)
        {
            $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

            $consulta = $objetoAccesoDato->RetornarConsulta("DELETE FROM cocheras WHERE id_cochera = :id");

            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
        } 

        public static function ObtenerCocheraPorId($id)
        {
            $cochera = array();
            $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

            $consulta = $objetoAccesoDato->RetornarConsulta("SELECT * FROM cocheras WHERE id_cochera = :id");

            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
            $cochera = $consulta->fetch();

            return $cochera;
        }
    }
?>