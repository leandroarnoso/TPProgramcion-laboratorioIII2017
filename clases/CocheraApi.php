<?PHP

    require_once 'Cochera.php';

    class CocheraApi 
    {

        public function TraerVacias ($request, $response) 
        {
            $status = 500; 

            if(!$arrayCocherasVacias = Cochera::ObtenerCocherasVaciasBD())
                $status = 404;
            else
                $status = 200;
                
            $response = $response->withJson($arrayCocherasVacias, $status);
                
            return $response;
        }

        public function TraerOcupadas($request, $response) 
        { 
            $status = 500; 

            if (!$arrayAutosCocheras = Cochera::ObtenerAutosCocherasBD())
                $status = 404;
            else
                $status = 200;

            $response = $response->withJson($arrayAutosCocheras, $status);

            return $response;
        }

        public function TraerUsos($request,$response) 
        { 
            $parametros = $request->getQueryParams();
            $status = 500;

            switch ($parametros["discapacitado"])
            {
                case "si":
                    $str = "cocheras.discapacitado = 'si'";
                    break;
                case "no":
                    $str = "cocheras.discapacitado = 'no'";
                    break;
                default:
                    $str = "1";
            }

            if($respuesta = Cochera::UsoDeCocheras($parametros["desde"], $parametros["hasta"], $str))
                $status = 200;
            else
                $status = 400;
                
            $response = $response->withJson($respuesta, $status);
            
            return $response;
        }

    }
?>