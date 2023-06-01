<?php
use Slim\Factory\AppFactory;

require 'App/src/Models/GenerosController.php';
require 'App/src/Models/PlataformaController.php';
require 'vendor/autoload.php';
require 'App/src/Models/DB.php';

$app = AppFactory::create();

//endpoints Generos (ver -> README.md)
$app->post('/generos', '\App\src\Models\GenerosController:create');
$app->get('/generos','\App\src\Models\GenerosController:list');
$app->put('/generos/{id}', '\App\src\Models\GenerosController:update');
$app->delete('/generos', '\App\src\Models\GenerosController:delete');

//endpoints Plataformas (ver -> README.md)
$app->post('/plataformas', '\App\src\Models\PlataformaController:create');
$app->get('/plataformas', '\App\src\Models\PlataformaController:list');
$app->put('/plataformas/{id}', '\App\src\Models\PlataformaController:update');
$app->delete('/plataformas/{id}', '\App\src\Models\PlataformaController:delete');

//endpoints Juegos (ver -> README.md)
$app->get('/juegosall', '\App\src\Models\PlataformaController:juegosAll');

$app->get('/juegos', '\App\src\Models\PlataformaController:juegos');

$app->post('/juegos', '\App\src\Models\PlataformaController:createJuego');

$app->put('/juegos/{id}', '\App\src\Models\PlataformaController:updateJuegos');

// Correr la aplicación
$app->run();