<?php

include_once('path/to/autoload.php');

use App\Config\Router;

$routes = new Router();

$routes->get('/', function() {
 echo "home";
});

$routes->get('/test', 'function() {
    echo "test";
   }');
$routes->get('/user', function() {
echo "user";
}, ['before'=>function(){echo "before";}, 'after' => function() {echo "ok";}]);

$routes->get('/test/[0-9]+/hello', function($a) {
echo "test" . $a . $_GET['url'];
});

$routes->post('/test', function() {
echo "test post";
});

// $routes->setMaintenance(function() {
//     echo "page";
// });

$routes->run();