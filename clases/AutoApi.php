<?PHP

    require_once 'Auto.php';

    class AutoApi 
    {
        
        public function TraerUno($request, $response) 
        {
            $parametros = $request->getQueryParams();
            $key = key($parametros);
            $auto = array();
            $status = 500;

            if (!$auto = Auto::ObtenerAuto($key, $parametros[$key]))
                $status = 404;
            else
                $status = 200;

            $response = $response->withJson($auto, $status);

            return $response;
        }
        
        public function TraerTodos($request, $response)
        {
            $parametros = $request->getQueryParams();
            $arrayAutos = array();
            $status = 500;

            if (!$arrayAutos= Auto::ObtenerAutosBD())
                $status = 404;
            else
                $status = 200;

            $response = $response->withJson($arrayAutos, $status);

            return $response;
        } 

        public function TraerRegistros($request, $response) 
        { 
            $parametros = $request->getQueryParams();
            $status = 500;

            if ($respuesta = Auto::RegistrosDeAutos($parametros["desde"], $parametros["hasta"]))
                $status = 200;
            else
                $status = 400;
                
            $response = $response->withJson($respuesta, $status);
            
            return $response;
        }

    }
?>
