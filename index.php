<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    require '/vendor/autoload.php';
    require_once '/clases/MWparaAutentificar.php';
    require_once '/clases/EmpleadoApi.php';
    require_once '/clases/UsuarioApi.php';
    require_once '/clases/AutoApi.php';
    require_once '/clases/CocheraApi.php';
    require_once '/clases/EstacionamientoApi.php';
    require_once '/bd/accesoAdatos.php';
    date_default_timezone_set("America/Argentina/Buenos_Aires"); 

    $config['displayErrorDetails'] = true;
    $config['addContentLengthHeader'] = false;
    
    $app = new \Slim\App(["settings" => $config]);


    $app->group('/usuario', function () {

        $this->get('[/]', \UsuarioApi::class . ':TraerLogins')
            ->add(\MWparaAutentificar::class . ':VerificarPerfil')
            ->add(\MWparaAutentificar::class . ':VerificarToken');

        $this->post('[/]', \UsuarioApi::class . ':ValidarUsuario');
    });


    $app->group('/empleado', function () {

        $this->get('[/]', \EmpleadoApi::class . ':TraerUno');

        $this->get('/lista[/]', \EmpleadoApi::class . ':TraerTodos');

        $this->get('/operaciones[/]', \EmpleadoApi::class . ':TraerOperaciones');

        $this->post('/alta[/]', \EmpleadoApi::class . ':Alta');

        $this->post('/suspender[/]', \EmpleadoApi::class . ':Suspender');  // put

        $this->post('/reintegrar[/]', \EmpleadoApi::class . ':Reintegrar');  // put

        $this->post('/baja[/]', \EmpleadoApi::class . ':Baja');  // delete

    })->add(\MWparaAutentificar::class . ':VerificarPerfil')->add(\MWparaAutentificar::class . ':VerificarToken');


    $app->group('/auto', function () {

        $this->get('[/]', \AutoApi::class . ':TraerUno');
        
        $this->get('/lista[/]', \AutoApi::class . ':TraerTodos')
            ->add(\MWparaAutentificar::class . ':VerificarPerfil');

        $this->get('/registros[/]', \AutoApi::class . ':TraerRegistros')
            ->add(\MWparaAutentificar::class . ':VerificarPerfil');
    
    })->add(\MWparaAutentificar::class . ':VerificarToken');


    $app->group('/cochera', function () {
 
        $this->get('/vacias[/]', \CocheraApi::class . ':TraerVacias');
        
        $this->get('/ocupadas[/]', \CocheraApi::class . ':TraerOcupadas'); 

        $this->get('/usos[/]', \CocheraApi::class . ':TraerUsos')
            ->add(\MWparaAutentificar::class . ':VerificarPerfil');

    })->add(\MWparaAutentificar::class . ':VerificarToken');


    $app->group('/estacionamiento', function () {

        $this->get('/facturacion[/]', \EstacionamientoApi::class . ':Facturar')
            ->add(\MWparaAutentificar::class . ':VerificarPerfil');

        $this->post('/estacionar[/]', \EstacionamientoApi::class . ':Estacionar');

        $this->post('/sacar[/]', \EstacionamientoApi::class . ':Sacar');  // put
        
    })->add(\MWparaAutentificar::class . ':VerificarToken');


    $app->add(function ($request, $response, $next) {
        $response = $next($request, $response);
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    });

    $app->run();
?>
