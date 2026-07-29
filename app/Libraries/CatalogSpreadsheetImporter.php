<?php

namespace App\Libraries;

use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class CatalogSpreadsheetImporter
{
    private const MAX_ROWS = 5000;

    private const MAX_COLUMNS = 100;

    /**
     * @var array<string, list<string>>
     */
    private const HEADER_ALIASES = [
        'name' => ['nombre producto', 'nombre', 'producto', 'descripcion'],
        'measurements' => ['medidas', 'medida', 'dimensiones'],
        'material' => ['material'],
        'packaging' => ['embalaje', 'presentacion', 'presentación'],
        'pack' => ['pack'],
        'master' => ['master'],
        'code' => ['codigo', 'código', 'cod', 'sku'],
        'price' => ['precio', 'precio unitario', 'importe'],
        'image' => ['@image', '@imagen', 'imagen', 'imagen producto', 'imagen_producto'],
        'category' => ['categoria', 'categoría', 'rubro'],
        'featured' => ['destacado', 'producto destacado'],
    ];

    private const REQUIRED_FIELDS = ['name', 'code', 'price', 'image'];

    /**
     * @return array<string, mixed>
     */
    public function import(string $path, string $extension): array
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new InvalidArgumentException('El archivo cargado no está disponible para lectura.');
        }

        $extension = strtolower($extension);
        if (! in_array($extension, ['xlsx', 'csv'], true)) {
            throw new InvalidArgumentException('Formato no permitido. Usá un archivo .xlsx o .csv.');
        }

        $spreadsheet = $this->loadSpreadsheet($path, $extension);

        try {
            return $this->normalizeSpreadsheet($spreadsheet);
        } finally {
            $spreadsheet->disconnectWorksheets();
        }
    }

    private function loadSpreadsheet(string $path, string $extension): Spreadsheet
    {
        if ($extension === 'csv') {
            /** @var Csv $reader */
            $reader = IOFactory::createReader('Csv');
            $reader->setDelimiter($this->detectCsvDelimiter($path));
            $reader->setEnclosure('"');
            $reader->setInputEncoding('UTF-8');
        } else {
            $reader = IOFactory::createReader('Xlsx');
        }

        $reader->setReadDataOnly($extension === 'csv');

        return $reader->load($path);
    }

    private function detectCsvDelimiter(string $path): string
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return ',';
        }

        $firstLine = fgets($handle, 8192) ?: '';
        fclose($handle);

        $scores = [
            ',' => substr_count($firstLine, ','),
            ';' => substr_count($firstLine, ';'),
            "\t" => substr_count($firstLine, "\t"),
        ];
        arsort($scores);
        $delimiter = (string) array_key_first($scores);

        return $scores[$delimiter] > 0 ? $delimiter : ',';
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeSpreadsheet(Spreadsheet $spreadsheet): array
    {
        $sheets = [];
        $selected = null;

        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $sheet = $this->inspectWorksheet($worksheet);
            $sheets[] = [
                'name' => $sheet['name'],
                'headerRow' => $sheet['headerRow'],
                'rowCount' => count($sheet['rows']),
                'isEmpty' => $sheet['rows'] === [],
            ];

            if ($selected === null && $sheet['headers'] !== []) {
                $selected = $sheet;
            }
        }

        if ($selected === null) {
            throw new InvalidArgumentException('El archivo no contiene una tabla con encabezados y productos.');
        }

        $missingColumns = array_values(array_filter(
            self::REQUIRED_FIELDS,
            static fn (string $field): bool => ! isset($selected['columnMap'][$field]),
        ));
        $globalWarnings = [];

        if (! isset($selected['columnMap']['category'])) {
            $globalWarnings[] = 'No se encontró una columna de categoría; deberá completarse antes de componer el catálogo.';
        }
        if ($selected['unknownHeaders'] !== []) {
            $globalWarnings[] = 'Hay columnas no reconocidas. Se conservan como referencia, pero todavía no alimentan el catálogo.';
        }

        $rows = $this->validateRows($selected['rows'], $missingColumns);
        $summary = [
            'totalRows' => count($rows),
            'validRows' => count(array_filter($rows, static fn (array $row): bool => $row['status'] === 'valid')),
            'warningRows' => count(array_filter($rows, static fn (array $row): bool => $row['status'] === 'warning')),
            'errorRows' => count(array_filter($rows, static fn (array $row): bool => $row['status'] === 'error')),
        ];

        return [
            'version' => 1,
            'activeSheet' => $selected['name'],
            'headerRow' => $selected['headerRow'],
            'headers' => $selected['headers'],
            'columnMap' => $selected['columnMap'],
            'missingColumns' => $missingColumns,
            'unknownHeaders' => $selected['unknownHeaders'],
            'globalWarnings' => $globalWarnings,
            'summary' => $summary,
            'rows' => $rows,
            'sheets' => $sheets,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function inspectWorksheet(Worksheet $worksheet): array
    {
        $highestRow = min($worksheet->getHighestDataRow(), self::MAX_ROWS + 1);
        $highestColumn = min(
            \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($worksheet->getHighestDataColumn()),
            self::MAX_COLUMNS,
        );
        $headerRow = $this->findHeaderRow($worksheet, $highestRow, $highestColumn);

        if ($headerRow === null) {
            return [
                'name' => $worksheet->getTitle(),
                'headerRow' => null,
                'headers' => [],
                'columnMap' => [],
                'unknownHeaders' => [],
                'rows' => [],
            ];
        }

        $headers = [];
        $columnMap = [];
        $unknownHeaders = [];

        for ($column = 1; $column <= $highestColumn; $column++) {
            $label = $this->readCell($worksheet, $column, $headerRow);
            if ($label === '') {
                continue;
            }

            $headers[] = [
                'column' => $column,
                'label' => $label,
                'field' => $this->resolveHeader($label),
            ];
            $field = $this->resolveHeader($label);

            if ($field === null) {
                $unknownHeaders[] = $label;
            } elseif (! isset($columnMap[$field])) {
                $columnMap[$field] = $column;
            }
        }

        $rows = [];
        for ($rowNumber = $headerRow + 1; $rowNumber <= $highestRow; $rowNumber++) {
            $raw = [];
            $hasValue = false;

            foreach ($headers as $header) {
                $value = $this->readCell($worksheet, $header['column'], $rowNumber);
                $raw[$header['label']] = $value;
                $hasValue = $hasValue || $value !== '';
            }

            if (! $hasValue) {
                continue;
            }

            $values = [];
            foreach ($columnMap as $field => $column) {
                $values[$field] = $this->readCell($worksheet, $column, $rowNumber);
            }

            $values['priceNumber'] = $this->parsePrice($values['price'] ?? '');
            $rows[] = [
                'sourceRow' => $rowNumber,
                'values' => $values,
                'raw' => $raw,
            ];
        }

        return [
            'name' => $worksheet->getTitle(),
            'headerRow' => $headerRow,
            'headers' => $headers,
            'columnMap' => $columnMap,
            'unknownHeaders' => $unknownHeaders,
            'rows' => $rows,
        ];
    }

    private function findHeaderRow(Worksheet $worksheet, int $highestRow, int $highestColumn): ?int
    {
        for ($row = 1; $row <= min($highestRow, 20); $row++) {
            $values = 0;
            for ($column = 1; $column <= $highestColumn; $column++) {
                if ($this->readCell($worksheet, $column, $row) !== '') {
                    $values++;
                }
            }

            if ($values >= 2) {
                return $row;
            }
        }

        return null;
    }

    private function resolveHeader(string $header): ?string
    {
        $normalized = $this->normalizeKey($header);

        foreach (self::HEADER_ALIASES as $field => $aliases) {
            foreach ($aliases as $alias) {
                if ($normalized === $this->normalizeKey($alias)) {
                    return $field;
                }
            }
        }

        return null;
    }

    private function normalizeKey(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $value = $transliterated === false ? $value : $transliterated;

        return trim(preg_replace('/[^a-z0-9@]+/', ' ', $value) ?? '');
    }

    private function cleanValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        $cleaned = preg_replace('/\s+/u', ' ', trim((string) $value));

        return $cleaned ?? '';
    }

    private function readCell(Worksheet $worksheet, int $column, int $row): string
    {
        $cell = $worksheet->getCell([$column, $row]);
        if ($cell->getDataType() === DataType::TYPE_FORMULA) {
            return '';
        }

        return $this->cleanValue($cell->getFormattedValue());
    }

    private function parsePrice(string $value): ?float
    {
        $numeric = preg_replace('/[^\d,.\-]/u', '', $value) ?? '';
        if ($numeric === '' || $numeric === '-') {
            return null;
        }

        $lastComma = strrpos($numeric, ',');
        $lastDot = strrpos($numeric, '.');

        if ($lastComma !== false && $lastDot !== false) {
            $decimalSeparator = $lastComma > $lastDot ? ',' : '.';
            $thousandsSeparator = $decimalSeparator === ',' ? '.' : ',';
            $numeric = str_replace($thousandsSeparator, '', $numeric);
            $numeric = str_replace($decimalSeparator, '.', $numeric);
        } elseif ($lastComma !== false) {
            $decimals = strlen($numeric) - $lastComma - 1;
            $numeric = $decimals > 0 && $decimals <= 2
                ? str_replace(',', '.', $numeric)
                : str_replace(',', '', $numeric);
        }

        return is_numeric($numeric) ? round((float) $numeric, 2) : null;
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @param list<string> $missingColumns
     *
     * @return list<array<string, mixed>>
     */
    private function validateRows(array $rows, array $missingColumns): array
    {
        $codeCounts = [];
        foreach ($rows as $row) {
            $code = $row['values']['code'] ?? '';
            if ($code !== '') {
                $codeCounts[$code] = ($codeCounts[$code] ?? 0) + 1;
            }
        }

        return array_map(function (array $row) use ($missingColumns, $codeCounts): array {
            $errors = [];
            $warnings = [];
            $values = $row['values'];

            foreach ($missingColumns as $field) {
                $errors[] = 'Falta la columna obligatoria ' . $this->fieldLabel($field) . '.';
            }

            foreach (self::REQUIRED_FIELDS as $field) {
                if (in_array($field, $missingColumns, true)) {
                    continue;
                }
                if (($values[$field] ?? '') === '') {
                    $errors[] = 'Falta ' . $this->fieldLabel($field) . '.';
                }
            }

            if (($values['price'] ?? '') !== '' && $values['priceNumber'] === null) {
                $errors[] = 'El precio no tiene un formato numérico válido.';
            }

            $code = $values['code'] ?? '';
            if ($code !== '' && ($codeCounts[$code] ?? 0) > 1) {
                $warnings[] = 'El código se repite en el archivo; se resolverá al asignar categorías.';
            }

            if (($values['category'] ?? '') === '') {
                $warnings[] = 'Categoría pendiente.';
            }

            $row['errors'] = $errors;
            $row['warnings'] = $warnings;
            $row['status'] = $errors !== [] ? 'error' : ($warnings !== [] ? 'warning' : 'valid');

            return $row;
        }, $rows);
    }

    private function fieldLabel(string $field): string
    {
        return [
            'name' => 'nombre',
            'code' => 'código',
            'price' => 'precio',
            'image' => 'imagen',
        ][$field] ?? $field;
    }
}
