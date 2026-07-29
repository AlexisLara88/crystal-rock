<?php

namespace App\Controllers;

use App\Libraries\CatalogPdfRenderer;
use App\Libraries\CatalogImageMatcher;
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

    public function matchCatalogImages(): ResponseInterface
    {
        try {
            $references = json_decode(
                (string) $this->request->getPost('imageReferences'),
                true,
                512,
                JSON_THROW_ON_ERROR,
            );

            if (! is_array($references) || $references === [] || count($references) > 5000) {
                throw new \InvalidArgumentException('No se recibieron referencias de imagen válidas.');
            }

            $normalizedReferences = [];
            foreach ($references as $reference) {
                if (! is_array($reference)) {
                    throw new \InvalidArgumentException('Las referencias de imagen no tienen el formato esperado.');
                }

                $normalizedReferences[] = [
                    'sourceRow' => (int) ($reference['sourceRow'] ?? 0),
                    'image' => trim((string) ($reference['image'] ?? '')),
                ];
            }

            $matcher = new CatalogImageMatcher();
            $assets = [];
            $zip = $this->request->getFile('imageBundle');

            if ($zip !== null && $zip->getError() !== UPLOAD_ERR_NO_FILE) {
                if (! $zip->isValid() || strtolower($zip->getClientExtension()) !== 'zip') {
                    throw new \InvalidArgumentException('Seleccioná un archivo ZIP válido.');
                }
                if ($zip->getSize() > 25 * 1024 * 1024) {
                    throw new \InvalidArgumentException('El ZIP supera el límite de 25 MB.');
                }
                if (! in_array($zip->getMimeType(), ['application/zip', 'application/x-zip-compressed', 'application/octet-stream'], true)) {
                    throw new \InvalidArgumentException('El contenido cargado no corresponde a un ZIP.');
                }

                $assets = array_merge($assets, $matcher->readZip($zip->getTempName()));
            }

            foreach ($this->request->getFileMultiple('imageFiles') ?? [] as $file) {
                if ($file->getError() === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                if (! $file->isValid()) {
                    throw new \InvalidArgumentException('Una de las imágenes no se pudo cargar.');
                }
                if ($file->getSize() > 12 * 1024 * 1024) {
                    throw new \InvalidArgumentException("La imagen {$file->getClientName()} supera el límite de 12 MB.");
                }

                $content = file_get_contents($file->getTempName());
                if ($content === false) {
                    throw new \InvalidArgumentException("No fue posible leer {$file->getClientName()}.");
                }

                $assets[] = [
                    'name' => $file->getClientName(),
                    'content' => $content,
                ];
            }

            if ($assets === []) {
                throw new \InvalidArgumentException('Seleccioná un ZIP o al menos una imagen.');
            }

            return $this->response->setJSON([
                'ok' => true,
                'result' => $matcher->match($normalizedReferences, $assets),
                'csrfHash' => csrf_hash(),
            ]);
        } catch (\JsonException | \InvalidArgumentException $exception) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => $exception->getMessage(),
                'csrfHash' => csrf_hash(),
            ]);
        } catch (\Throwable $exception) {
            log_message('error', 'No fue posible revisar las imágenes: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'No fue posible procesar las imágenes cargadas.',
                'csrfHash' => csrf_hash(),
            ]);
        }
    }
}
