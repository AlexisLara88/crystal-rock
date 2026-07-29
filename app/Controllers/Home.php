<?php

namespace App\Controllers;

use App\Libraries\CatalogPdfRenderer;
use App\Libraries\CatalogPresentationContract;
use App\Libraries\CatalogSpreadsheetImporter;
use CodeIgniter\HTTP\ResponseInterface;

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

    public function importCatalog(): ResponseInterface
    {
        $file = $this->request->getFile('catalogFile');

        if ($file === null || ! $file->isValid()) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'Seleccioná un archivo .xlsx o .csv válido.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'El archivo supera el límite de 5 MB para la demo.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $extension = strtolower($file->getClientExtension());
        if (! in_array($extension, ['xlsx', 'csv'], true)) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'Formato no permitido. Usá un archivo .xlsx o .csv.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $allowedMimes = $extension === 'xlsx'
            ? [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/zip',
                'application/octet-stream',
            ]
            : ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'];

        if (! in_array($file->getMimeType(), $allowedMimes, true)) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'El contenido del archivo no coincide con su extensión.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        try {
            $result = (new CatalogSpreadsheetImporter())->import($file->getTempName(), $extension);

            return $this->response->setJSON([
                'ok' => true,
                'filename' => $file->getClientName(),
                'result' => $result,
                'csrfHash' => csrf_hash(),
            ]);
        } catch (\Throwable $exception) {
            log_message('error', 'No fue posible importar el catálogo: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'No fue posible leer el archivo. Verificá que no esté dañado y que tenga encabezados.',
                'csrfHash' => csrf_hash(),
            ]);
        }
    }
}
