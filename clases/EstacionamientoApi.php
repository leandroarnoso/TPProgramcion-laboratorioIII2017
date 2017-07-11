<?PHP

    require_once 'Estacionamiento.php';

    class EstacionamientoApi 
    {

        public function Estacionar($request, $response) 
        {
            $pedido = $request->getParsedBody();
            $id_auto = $pedido["id_auto"];
            $status = 500;

            if (!$id_auto)
            {
                $auto = new Auto($pedido["patente"], $pedido["marca"], $pedido["color"]);
                $id_auto = $auto->AgregarAutoBD();
            }

            if($id = Estacionamiento::EstacionarAuto($id_auto, $pedido["id_cochera"], $pedido["id_emp"]))
            {
                $respuesta["mensaje"] = "El auto fue estacionado exitosamente";
                $status = 200;
            }
            else
            {
                $respuesta["mensaje"] = "No se ha podido estacionar el auto debido a que ya esta estacionado
                    o la cochera esta ocupada";
                $status= 400;
            }
            
            $response = $response->withJson($respuesta, $status);

            return $response;
        }

        public function Sacar($request, $response) 
        {
            $pedido = $request->getParsedBody();
            $status = 500;

            if(!$respuesta = Estacionamiento::SacarAuto($pedido["id_auto"], $pedido["id_cochera"], $pedido["id_emp"]))
            {
                $respuesta["mensaje"] = "No se ha podido sacar el auto";
                $status = 404; 
            }
            else
                $status = 200;


            $response = $response->withJson($respuesta, $status);

            return $response;
        }

        public function Facturar($request, $response)
        {
            $parametros = $request->getQueryParams();
            $status = 500;

            if ($respuesta = Estacionamiento::ObtenerFacturacion($parametros["desde"], $parametros["hasta"]))
                $status = 200;
            else
                $status = 400;
                
            $response = $response->withJson($respuesta, $status);
            
            return $response;
        }

    }
?>