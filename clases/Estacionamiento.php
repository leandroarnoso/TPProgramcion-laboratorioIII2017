<?PHP
    require_once "Cochera.php";
    require_once "Auto.php";

    class Estacionamiento
    {
    //--------------------------------------------------------------------------------//
    //--ATRIBUTOS
    //--------------------------------------------------------------------------------//


    //--------------------------------------------------------------------------------//
    //--GETTERS
    //--------------------------------------------------------------------------------//
    //--CONSTRUCTOR
    //--------------------------------------------------------------------------------//


    //--------------------------------------------------------------------------------//
    //--TO STRING
    //--------------------------------------------------------------------------------//


    //--------------------------------------------------------------------------------//
    //--METODOS DE INSTANCIA
        
    //--------------------------------------------------------------------------------//
    //--METODOS DE CLASE
        public static function EstacionarAuto($id_auto, $id_cochera, $id_emp_ingreso)
        {
            $id = 0;
            $fecha_ingreso = date("Y-m-d H:i:s");
            $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

            if (self::EsPosibleEstacionar($id_auto, $id_cochera))
            {        
                $consulta =$objetoAccesoDato->RetornarConsulta("INSERT INTO registros (id_auto, id_cochera, id_emp_ingreso, fecha_ingreso) 
                    VALUES(:id_auto, :id_cochera, :id_emp_ingreso, :fecha_ingreso)");

                $consulta->bindValue(':id_auto', $id_auto, PDO::PARAM_INT);
                $consulta->bindValue(':id_cochera', $id_cochera, PDO::PARAM_INT);
                $consulta->bindValue(':id_emp_ingreso', $id_emp_ingreso, PDO::PARAM_INT);
                $consulta->bindValue(':fecha_ingreso', $fecha_ingreso, PDO::PARAM_STR);
                $consulta->execute();
                $id = $objetoAccesoDato->ObtenerUltimoId();
            }

            return $id;
        }

        public static function SacarAuto($id_auto, $id_cochera, $id_emp_egreso)
        {
            $fecha_salida = date("Y-m-d H:i:s");
            $monto = self::CalcularPago(self::CalcularTiempoEstacionado($id_auto, $id_cochera, $fecha_salida));
            $auto = array();

            if ($monto > 0)
            {
                $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

                $consulta =$objetoAccesoDato->RetornarConsulta("UPDATE registros 
                    SET id_emp_egreso = :id_emp_egreso, fecha_salida = :fecha_salida, monto = :monto 
                    WHERE id_auto = :id_auto AND id_cochera = :id_cochera AND fecha_salida IS NULL");

                $consulta->bindValue(':id_auto', $id_auto, PDO::PARAM_INT);
                $consulta->bindValue(':id_cochera', $id_cochera, PDO::PARAM_INT);
                $consulta->bindValue(':id_emp_egreso', $id_emp_egreso, PDO::PARAM_INT);
                $consulta->bindValue(':fecha_salida', $fecha_salida, PDO::PARAM_STR);
                $consulta->bindValue(':monto', $monto, PDO::PARAM_STR);
                $consulta->execute();
                
                $auto = Auto::ObtenerAuto("id_auto", $id_auto);
                $auto["monto"] = $monto;
            }

            return $auto;
        }

        public static function ObtenerFacturacion($desde, $hasta)
        {
            $facturacion = array();
            $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

            $consulta =$objetoAccesoDato->RetornarConsulta("SELECT COUNT(distinct id_auto) AS cantidad_vehiculos, 
                round(SUM(monto), 2) AS total FROM registros WHERE fecha_salida BETWEEN :desde AND :hasta");

            $consulta->bindValue(':desde', $desde, PDO::PARAM_STR);
            $consulta->bindValue(':hasta', $hasta, PDO::PARAM_STR);
            $consulta->execute();
            $consulta->setFetchMode(PDO::FETCH_ASSOC);
            $facturacion = $consulta->fetch();
            
            return $facturacion;
        }

        private static function CalcularTiempoEstacionado($id_auto, $id_cochera, $fecha_salida)
        {
            $tiempoEstacionado = 0;
            $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

            $consulta =$objetoAccesoDato->RetornarConsulta("SELECT fecha_ingreso FROM registros 
                WHERE id_auto = :id_auto AND id_cochera = :id_cochera AND fecha_salida IS NULL");

            $consulta->bindValue(':id_auto', $id_auto, PDO::PARAM_INT);
            $consulta->bindValue(':id_cochera', $id_cochera, PDO::PARAM_INT);
            $consulta->execute();
            if ($dato = $consulta->fetch())
                $tiempoEstacionado = strtotime($fecha_salida) - strtotime($dato[0]);

            return $tiempoEstacionado / 60;
        }

        private static function CalcularPago($tiempoEstacionado)
        {
            $pago = 0;
            // tiempoEstacionado en minutos
            if ($tiempoEstacionado > 0)
            {
                $pago = floor($tiempoEstacionado / 1440) * 170;   // Calcular pago por estadia completa
                $pago += floor(($tiempoEstacionado % 1440) / 720) * 90; // Sumar el pago de la por media estadia
                $pago += (($tiempoEstacionado % 1440) %720) * (1 / 6); // Sumar el pago por hora
            }

            return round($pago, 2);
        }

        private static function EsPosibleEstacionar($id_auto, $id_cochera)
        {
            $retorno = false;
            $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

            $consulta =$objetoAccesoDato->RetornarConsulta("SELECT registros.id_cochera, registros.id_auto FROM registros 
                WHERE (registros.id_auto = :id_auto OR registros.id_cochera = :id_cochera) AND fecha_salida IS NULL");

            $consulta->bindValue(':id_auto', $id_auto, PDO::PARAM_INT);
            $consulta->bindValue(':id_cochera', $id_cochera, PDO::PARAM_INT);
            $consulta->execute();
            if(!$dato = $consulta->fetch())
                $retorno = true;

            return $retorno;
        }

    }
?>