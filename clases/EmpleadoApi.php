<?PHP

    require_once 'Empleado.php';

    class EmpleadoApi 
    {

        public function TraerUno($request, $response) 
        {
            $parametros = $request->getQueryParams();
            $key = key($parametros);
            $empleado = array();
            $status = 500;

            if (!$empleado = Empleado::ObtenerEmpleado($key, $parametros[$key]))
                $status = 404;
            else
                $status = 200;

            $response = $response->withJson($empleado, $status);

            return $response;
        }

        public function TraerTodos($request, $response) 
        { 
            $status = 500; 

            if (!$arrayEmpleados = Empleado::ObtenerEmpleadosBD())
                $status = 404;
            else 
                $status = 200;

            $response = $response->withJson($arrayEmpleados, $status);

            return $response;
        }

        public function TraerOperaciones($request, $response) 
        { 
            $parametros = $request->getQueryParams();

            $status = 500;
            if($parametros["id_emp"])
            {
                $respuesta = Empleado::ObtenerOperacionesIngresos($parametros["id_emp"], $parametros["desde"], $parametros["hasta"]);
                $respuesta += Empleado::ObtenerOperacionesSalidas($parametros["id_emp"], $parametros["desde"], $parametros["hasta"]);
                $status = 200;
            }
            else 
            {
                $respuesta["mensaje"] = "No se ha podido encontrar al empleado";
                $status = 404;
            }

            $response = $response->withJson($respuesta, $status);

            return $response;
        }

        public function Alta($request, $response) 
        {
            $pedido = $request->getParsedBody();
            $status = 500;

            $empleado = new Empleado($pedido["dni"], $pedido["apellido"], $pedido["nombre"], $pedido["sexo"], $pedido["legajo"], 
                $pedido["sueldo"], $pedido["turno"]);
            if (!$respuesta = $empleado->AgregarEmpleadoBD())
            {
                $respuesta["mensaje"] = "El empleado no pudo ser dado de alta";
                $status = 400;
            }
            else
            {
                $respuesta["mensaje"] = "El empleado ha sido dado de alta";
                $status = 200;
            }
                
            $response = $response->withJson($respuesta, $status);

            return $response;
        }

        public function Suspender($request, $response) 
        {
            $pedido = $request->getParsedBody();
            $status = 500;

            if (!$respuesta = Empleado::SuspenderEmpleadoBD($pedido["id_emp"], $pedido["descripcion"]))
            {
                $respuesta["mensaje"] = "No se ha podido suspender al empleado";
                $status = 400;
            }
            else
            {
                $respuesta["mensaje"] = "Se ha suspendido al empleado";
                $status = 200;
            }
                
            $response = $response->withJson($respuesta, $status);

            return $response;
        }

        public function Reintegrar($request, $response) 
        {
            $pedido = $request->getParsedBody();
            $status = 500;

            if (!$respuesta = Empleado::ReintegrarEmpleadoBD($pedido["id_emp"]))
            {
                $respuesta["mensaje"] = "No se ha podido reintegrar al empleado";
                $status = 400;
            }
            else
            {
                $respuesta["mensaje"] = "Se ha reintegrado al empleado";
                $status = 200;
            }
                
            $response = $response->withJson($respuesta, $status);

            return $response;
        }

        public function Baja($request, $response) 
        {
            $pedido = $request->getParsedBody();
            $status = 500;

            if (!$respuesta = Empleado::DarDeBajaEmpleadoBD($pedido["id_emp"],  $pedido["descripcion"]))
            {
                $respuesta["mensaje"] = "El empleado no se ha podido dar de baja";
                $status = 400;
            }
            else
            {
                $respuesta["mensaje"] = "El empleado ha sido dado de baja con exito";
                $status = 200;
            }
                
            $response = $response->withJson($respuesta, $status);

            return $response;
        }

    }
?>