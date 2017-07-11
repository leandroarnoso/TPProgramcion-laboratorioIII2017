<?PHP
    
    abstract class Vehiculo
    {
    //--------------------------------------------------------------------------------//
    //--ATRIBUTOS
        protected $_patente;
        protected $_marca;
        protected $_color;
    //--------------------------------------------------------------------------------//

    //--------------------------------------------------------------------------------//
    //--GETTERS
        public function GetPatente()
        {
            return $this->_patente;
        }

        public function GetMarca()
        {
            return $this->_marca;
        }

        public function GetColor()
        {
            return $this->_color;
        }
    //--------------------------------------------------------------------------------//
    //--CONSTRUCTOR
        public function __construct($patente, $marca, $color)
        {
            if ($patente !== NULL && $marca !== NULL && $color !== NULL)
            {
                $this->_patente = $patente;
                $this->_marca = $marca;
                $this->_color = $color;
            }
        }
    //--------------------------------------------------------------------------------//
    //--TO STRING
        public function ToString()
        {
            $str = "";

            $str .= "Patente: " . $this->_patente;
            $str .= " Marca: " . $this->_marca;
            $str .= " Color: " . $this->_color;

            return $str;
        }
    }
?>