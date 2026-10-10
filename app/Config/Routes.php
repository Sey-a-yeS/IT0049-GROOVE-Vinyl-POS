<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->setAutoRoute(false);

$routes->get('/', 'Home::index');
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attempt');

$routes->group('', ['filter' => 'auth'], static function ($routes): void {
    $routes->get('dashboard', 'Dashboard::index');
    $routes->post('logout', 'Auth::logout');

    $routes->get('products', 'Products::index');
    $routes->get('products/new', 'Products::new');
    $routes->post('products', 'Products::create');
    $routes->get('products/(:num)', 'Products::show/$1');
    $routes->get('products/(:num)/edit', 'Products::edit/$1');
    $routes->post('products/(:num)', 'Products::update/$1');
    $routes->post('products/(:num)/archive', 'Products::archive/$1');

    $routes->get('customers', 'Customers::index');
    $routes->get('customers/new', 'Customers::new');
    $routes->post('customers', 'Customers::create');
    $routes->get('customers/(:num)', 'Customers::show/$1');
    $routes->get('customers/(:num)/edit', 'Customers::edit/$1');
    $routes->post('customers/(:num)', 'Customers::update/$1');
    $routes->post('customers/(:num)/archive', 'Customers::archive/$1');

    $routes->get('staff', 'Staff::index');
    $routes->get('staff/new', 'Staff::new');
    $routes->post('staff', 'Staff::create');
    $routes->get('staff/(:num)', 'Staff::show/$1');
    $routes->get('staff/(:num)/edit', 'Staff::edit/$1');
    $routes->post('staff/(:num)', 'Staff::update/$1');
    $routes->post('staff/(:num)/archive', 'Staff::archive/$1');

    $routes->get('sales/new', 'Sales::new');
    $routes->post('sales', 'Sales::create');
    $routes->get('sales/history', 'Sales::history');
});
