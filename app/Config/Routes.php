<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('/demo/catalogo', 'Home::index');
$routes->get('/demo/catalogo/pdf-prueba', 'Home::pdfProof');
