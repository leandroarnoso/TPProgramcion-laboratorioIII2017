<?php
    class AccesoDatos
    {
        private static $_objetoAccesoDatos;
        private $_objetoPDO;
    
        private function __construct()
        {
            try {
    
                $this->_objetoPDO = new PDO('mysql:host=localhost;dbname=id1354930_estacionamiento;charset=utf8', 
                    'id1354930_leandro', 'estacionamiento',
                    array(PDO::ATTR_EMULATE_PREPARES => false,PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    
                $this->_objetoPDO->exec("SET CHARACTER SET utf8");
    
            } catch (PDOException $e) {
    
                print "Error!!!<br/>" . $e->getMessage();
    
                die();
            }
        }
    
        public function RetornarConsulta($sql)
        {
            return $this->_objetoPDO->prepare($sql);
        }

        public function ObtenerUltimoId()
        { 
            return $this->_objetoPDO->lastInsertId(); 
        }
    
        public static function DameUnObjetoAcceso()//patron singleton :un solo objeto instanciado
        {//devuelve la conexiona base de datos
            if (!isset(self::$_objetoAccesoDatos)) {       
                self::$_objetoAccesoDatos = new AccesoDatos(); 
            }
    
            return self::$_objetoAccesoDatos;        
        }
    
        // Evita que el objeto se pueda clonar
        public function __clone()
        {
            trigger_error('La clonaci&oacute;n de este objeto no est&aacute; permitida!!!', E_USER_ERROR);
        }
    }
?>