<?PHP
    
    abstract class Persona
    {
    //--------------------------------------------------------------------------------//
    //--ATRIBUTOS
        protected $_dni;
        protected $_apellido;
        protected $_nombre;
        protected $_sexo;
    //--------------------------------------------------------------------------------//

    //--------------------------------------------------------------------------------//
    //--GETTERS
        public function GetDni()
        {
            return $this->_dni;
        }

        public function GetApellido()
        {
            return $this->_apellido;
        }

        public function GetNombre()
        {
            return $this->_nombre;
        }

        public function GetSexo()
        {
            return $this->_sexo;
        }
    //--------------------------------------------------------------------------------//
    //--CONSTRUCTOR
        public function __construct($dni, $apellido, $nombre, $sexo)
        {
            if ($dni !== NULL && $apellido != NULL && $nombre !== NULL && $sexo !== NULL)
            {
                $this->_dni = $dni;
                $this->_apellido = $apellido;
                $this->_nombre = $nombre;
                $this->_sexo = $sexo;
            }
        }
    //--------------------------------------------------------------------------------//
    //--TO STRING
        public function ToString()
        {
            $str = "";

            $str .= $this->_dni . " - ";
            $str .= $this->_apellido . " - ";
            $str .= $this->_nombre . " - ";
            $str .= $this->_sexo;

            return $str;
        }
    }
?>