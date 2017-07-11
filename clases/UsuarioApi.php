<?PHP

    require_once 'Usuario.php';

    class UsuarioApi 
    {

        public function TraerLogins ($request, $response) 
        { 
            $parametros = $request->getQueryParams();
            $status = 500; 

            if (!$logins = Usuario::ObtenerLogins($parametros["id_emp"], $parametros["desde"], $parametros["hasta"]))
                $status = 404;
            else
                $status = 200;

            $response = $response->withJson($logins, $status);

            return $response;
        }

        public function ValidarUsuario($request, $response) 
        {
            $pedido = $request->getParsedBody();

            $status = 500;

            if (!$respuesta = Usuario::ValidarUsuario($pedido["usuario"], $pedido["pass"]))
            {
                $respuesta["mensaje"] = "usuario o contraseña invalida";
                $status = 404;
            }
            else
                $status = 200;
                
            $response = $response->withJson($respuesta, $status);

            return $response;
        }

    }

?>