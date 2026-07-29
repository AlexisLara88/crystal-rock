<?php

namespace App\Controllers;

use App\Libraries\CatalogPdfRenderer;
use App\Libraries\CatalogPresentationContract;

class Home extends BaseController
{
    public function index(): string
    {
        return view('catalog_demo', [
            'pageScript' => 'catalog-demo.js',
        ]);
    }

    public function pdfProof()
    {
        $pdf = (new CatalogPdfRenderer())->render(CatalogPresentationContract::proof());

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="crystal-rock-prueba-ev7.pdf"')
            ->setBody($pdf);
    }
}
