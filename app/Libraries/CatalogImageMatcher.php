<?php

namespace App\Libraries;

use InvalidArgumentException;
use ZipArchive;

final class CatalogImageMatcher
{
    private const MAX_FILES = 250;

    private const MAX_FILE_BYTES = 12 * 1024 * 1024;

    private const MAX_TOTAL_BYTES = 40 * 1024 * 1024;

    private const LOW_RESOLUTION_PIXELS = 640000;

    private const MAX_IMAGE_PIXELS = 40000000;

    private const ALLOWED_MIMES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    /**
     * @return list<array{name: string, content: string}>
     */
    public function readZip(string $path): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new InvalidArgumentException('El ZIP no se pudo abrir.');
        }

        try {
            $assets = [];
            $totalBytes = 0;

            for ($index = 0; $index < $zip->numFiles; $index++) {
                $stat = $zip->statIndex($index);
                if ($stat === false) {
                    continue;
                }

                $name = str_replace('\\', '/', (string) $stat['name']);
                if (
                    str_ends_with($name, '/')
                    || str_starts_with($name, '__MACOSX/')
                    || str_contains($name, '/../')
                    || str_starts_with($name, '../')
                ) {
                    continue;
                }

                $size = (int) ($stat['size'] ?? 0);
                if ($size > self::MAX_FILE_BYTES) {
                    throw new InvalidArgumentException("La imagen {$name} supera el límite de 12 MB.");
                }

                $totalBytes += $size;
                if ($totalBytes > self::MAX_TOTAL_BYTES) {
                    throw new InvalidArgumentException('Las imágenes descomprimidas superan el límite total de 40 MB.');
                }

                $content = $zip->getFromIndex($index);
                if ($content === false) {
                    continue;
                }

                $assets[] = ['name' => $name, 'content' => $content];
                if (count($assets) > self::MAX_FILES) {
                    throw new InvalidArgumentException('El ZIP contiene más de 250 archivos.');
                }
            }

            return $assets;
        } finally {
            $zip->close();
        }
    }

    /**
     * @param list<array{sourceRow: int, image: string}> $references
     * @param list<array{name: string, content: string}>  $files
     *
     * @return array<string, mixed>
     */
    public function match(array $references, array $files): array
    {
        if (count($files) > self::MAX_FILES) {
            throw new InvalidArgumentException('Se pueden revisar hasta 250 archivos por carga.');
        }

        $assets = [];
        $rejectedFiles = [];
        $totalBytes = 0;

        foreach ($files as $index => $file) {
            $name = $this->normalizeDisplayName($file['name'] ?? '');
            $content = $file['content'] ?? '';
            $size = strlen($content);
            $totalBytes += $size;

            if ($totalBytes > self::MAX_TOTAL_BYTES) {
                throw new InvalidArgumentException('Las imágenes superan el límite total de 40 MB.');
            }

            try {
                $assets[] = $this->inspectImage($index, $name, $content);
            } catch (InvalidArgumentException $exception) {
                $rejectedFiles[] = [
                    'name' => $name !== '' ? $name : 'Archivo sin nombre',
                    'reason' => $exception->getMessage(),
                ];
            }
        }

        $fullIndex = [];
        $basenameIndex = [];
        foreach ($assets as $assetIndex => $asset) {
            $fullIndex[$asset['key']][] = $assetIndex;
            $basenameIndex[$asset['basenameKey']][] = $assetIndex;
        }

        $referenceCounts = [];
        foreach ($references as $reference) {
            $key = $this->normalizeKey($reference['image'] ?? '');
            if ($key !== '') {
                $referenceCounts[$key] = ($referenceCounts[$key] ?? 0) + 1;
            }
        }

        $usedAssets = [];
        $rows = [];

        foreach ($references as $reference) {
            $sourceRow = (int) ($reference['sourceRow'] ?? 0);
            $imageReference = trim((string) ($reference['image'] ?? ''));
            $key = $this->normalizeKey($imageReference);
            $warnings = [];

            if ($key === '') {
                $rows[] = [
                    'sourceRow' => $sourceRow,
                    'reference' => $imageReference,
                    'status' => 'missing',
                    'image' => null,
                    'candidates' => [],
                    'warnings' => [],
                    'errors' => ['La fila no declara una imagen en @Image.'],
                ];
                continue;
            }

            $candidateIndexes = $fullIndex[$key] ?? [];
            if ($candidateIndexes === []) {
                $candidateIndexes = $basenameIndex[basename($key)] ?? [];
            }

            if ($candidateIndexes === []) {
                $rows[] = [
                    'sourceRow' => $sourceRow,
                    'reference' => $imageReference,
                    'status' => 'missing',
                    'image' => null,
                    'candidates' => [],
                    'warnings' => [],
                    'errors' => ["No se cargó el archivo {$imageReference}."],
                ];
                continue;
            }

            if (count($candidateIndexes) > 1) {
                $rows[] = [
                    'sourceRow' => $sourceRow,
                    'reference' => $imageReference,
                    'status' => 'duplicate',
                    'image' => null,
                    'candidates' => array_map(
                        static fn (int $assetIndex): string => $assets[$assetIndex]['name'],
                        $candidateIndexes,
                    ),
                    'warnings' => [],
                    'errors' => ['Hay más de un archivo que coincide con esta referencia.'],
                ];
                continue;
            }

            $assetIndex = $candidateIndexes[0];
            $asset = $assets[$assetIndex];
            $usedAssets[$assetIndex] = true;

            if ($asset['lowResolution']) {
                $warnings[] = "Resolución baja: {$asset['width']} × {$asset['height']} px.";
            }
            if (($referenceCounts[$key] ?? 0) > 1) {
                $warnings[] = 'La misma imagen está referenciada por más de una fila.';
            }

            $rows[] = [
                'sourceRow' => $sourceRow,
                'reference' => $imageReference,
                'status' => $warnings === [] ? 'matched' : 'warning',
                'image' => $this->publicAsset($asset),
                'candidates' => [],
                'warnings' => $warnings,
                'errors' => [],
            ];
        }

        $unusedFiles = [];
        foreach ($assets as $assetIndex => $asset) {
            if (! isset($usedAssets[$assetIndex])) {
                $unusedFiles[] = $this->publicAsset($asset, false);
            }
        }

        return [
            'version' => 1,
            'summary' => [
                'receivedFiles' => count($files),
                'usableFiles' => count($assets),
                'matchedRows' => count(array_filter(
                    $rows,
                    static fn (array $row): bool => in_array($row['status'], ['matched', 'warning'], true),
                )),
                'warningRows' => count(array_filter(
                    $rows,
                    static fn (array $row): bool => $row['status'] === 'warning',
                )),
                'missingRows' => count(array_filter(
                    $rows,
                    static fn (array $row): bool => $row['status'] === 'missing',
                )),
                'duplicateRows' => count(array_filter(
                    $rows,
                    static fn (array $row): bool => $row['status'] === 'duplicate',
                )),
                'unusedFiles' => count($unusedFiles),
                'rejectedFiles' => count($rejectedFiles),
            ],
            'rows' => $rows,
            'unusedFiles' => $unusedFiles,
            'rejectedFiles' => $rejectedFiles,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function inspectImage(int $id, string $name, string $content): array
    {
        if ($name === '' || $content === '') {
            throw new InvalidArgumentException('El archivo está vacío.');
        }
        if (strlen($content) > self::MAX_FILE_BYTES) {
            throw new InvalidArgumentException('La imagen supera el límite de 12 MB.');
        }

        $info = @getimagesizefromstring($content);
        if ($info === false || ! in_array($info['mime'] ?? '', self::ALLOWED_MIMES, true)) {
            throw new InvalidArgumentException('No es una imagen JPG, PNG o WebP válida.');
        }

        $width = (int) $info[0];
        $height = (int) $info[1];
        if (($width * $height) > self::MAX_IMAGE_PIXELS) {
            throw new InvalidArgumentException('La imagen supera el límite de 40 megapíxeles.');
        }

        return [
            'id' => $id,
            'name' => $name,
            'key' => $this->normalizeKey($name),
            'basenameKey' => basename($this->normalizeKey($name)),
            'mime' => (string) $info['mime'],
            'width' => $width,
            'height' => $height,
            'size' => strlen($content),
            'lowResolution' => ($width * $height) < self::LOW_RESOLUTION_PIXELS,
            'preview' => $this->createPreview($content),
        ];
    }

    private function createPreview(string $content): string
    {
        $source = @imagecreatefromstring($content);
        if ($source === false) {
            throw new InvalidArgumentException('No fue posible procesar la imagen.');
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $scale = min(140 / $sourceWidth, 105 / $sourceHeight, 1);
        $width = max(1, (int) round($sourceWidth * $scale));
        $height = max(1, (int) round($sourceHeight * $scale));
        $preview = imagecreatetruecolor(140, 105);
        $background = imagecolorallocate($preview, 252, 249, 243);
        imagefill($preview, 0, 0, $background);
        imagecopyresampled(
            $preview,
            $source,
            (int) floor((140 - $width) / 2),
            (int) floor((105 - $height) / 2),
            0,
            0,
            $width,
            $height,
            $sourceWidth,
            $sourceHeight,
        );

        ob_start();
        imagejpeg($preview, null, 76);
        $jpeg = (string) ob_get_clean();
        imagedestroy($preview);
        imagedestroy($source);

        return 'data:image/jpeg;base64,' . base64_encode($jpeg);
    }

    /**
     * @param array<string, mixed> $asset
     *
     * @return array<string, mixed>
     */
    private function publicAsset(array $asset, bool $includePreview = true): array
    {
        $public = [
            'name' => $asset['name'],
            'mime' => $asset['mime'],
            'width' => $asset['width'],
            'height' => $asset['height'],
            'size' => $asset['size'],
            'lowResolution' => $asset['lowResolution'],
        ];

        if ($includePreview) {
            $public['preview'] = $asset['preview'];
        }

        return $public;
    }

    private function normalizeDisplayName(string $name): string
    {
        return trim(str_replace('\\', '/', $name), " \t\n\r\0\x0B/");
    }

    private function normalizeKey(string $name): string
    {
        $name = $this->normalizeDisplayName($name);
        $segments = array_values(array_filter(
            explode('/', $name),
            static fn (string $segment): bool => $segment !== '' && $segment !== '.',
        ));

        if (in_array('..', $segments, true)) {
            return '';
        }

        return mb_strtolower(implode('/', $segments), 'UTF-8');
    }
}
