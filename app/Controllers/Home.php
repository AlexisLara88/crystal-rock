<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('catalog_demo', [
            'pageScript' => 'catalog-demo.js',
        ]);
    }
}
