<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index', ['filter' => 'csrf']);
$routes->get('/demo/catalogo', 'Home::index', ['filter' => 'csrf']);
$routes->get('/demo/catalogo/pdf-prueba', 'Home::pdfProof');
$routes->post('/demo/catalogo/importar', 'Home::importCatalog', ['filter' => 'csrf']);
$routes->post('/demo/catalogo/imagenes', 'Home::matchCatalogImages', ['filter' => 'csrf']);
