<?php

use App\Libraries\CatalogSpreadsheetImporter;
use CodeIgniter\Test\CIUnitTestCase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * @internal
 */
final class CatalogSpreadsheetImporterTest extends CIUnitTestCase
{
    public function testImportsXlsxWithAliasesPricesAndWarnings(): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Productos');
        $sheet->fromArray([
            ['Catálogo temporal'],
            ['Nombre producto', 'Codigo', 'Precio', '@Image', 'Medidas', 'Material'],
            ['Copa Gin', '00123', 2526.72, '1.jpg', '8 × 22 cm', 'Vidrio'],
            ['Copa Vino', '00123', 1642.32, '2.jpg', '7 × 21 cm', 'Vidrio'],
        ]);
        $sheet->getStyle('C3:C4')->getNumberFormat()->setFormatCode('$#,##0.00');
        $sheet->getStyle('B3:B4')->getNumberFormat()->setFormatCode('00000');
        $path = $this->temporaryPath('xlsx');

        try {
            (new Xlsx($spreadsheet))->save($path);
            $result = (new CatalogSpreadsheetImporter())->import($path, 'xlsx');
        } finally {
            $spreadsheet->disconnectWorksheets();
            @unlink($path);
        }

        $this->assertSame('Productos', $result['activeSheet']);
        $this->assertSame(2, $result['headerRow']);
        $this->assertSame(2, $result['summary']['totalRows']);
        $this->assertSame(2, $result['summary']['warningRows']);
        $this->assertSame(0, $result['summary']['errorRows']);
        $this->assertSame('00123', $result['rows'][0]['values']['code']);
        $this->assertSame('$2,526.72', $result['rows'][0]['values']['price']);
        $this->assertEquals(2526.72, $result['rows'][0]['values']['priceNumber']);
        $this->assertNotEmpty(array_filter(
            $result['globalWarnings'],
            static fn (string $warning): bool => str_contains($warning, 'categoría'),
        ));
        $this->assertStringContainsString('se repite', $result['rows'][0]['warnings'][0]);
    }

    public function testImportsSemicolonCsvAndBlocksMissingRequiredColumn(): void
    {
        $path = $this->temporaryPath('csv');
        file_put_contents(
            $path,
            "Producto;Codigo;Precio\nCopa de agua;A-10;1299,50\n",
        );

        try {
            $result = (new CatalogSpreadsheetImporter())->import($path, 'csv');
        } finally {
            @unlink($path);
        }

        $this->assertContains('image', $result['missingColumns']);
        $this->assertSame(1, $result['summary']['errorRows']);
        $this->assertSame('error', $result['rows'][0]['status']);
        $this->assertStringContainsString('columna obligatoria imagen', $result['rows'][0]['errors'][0]);
        $this->assertEquals(1299.50, $result['rows'][0]['values']['priceNumber']);
    }

    private function temporaryPath(string $extension): string
    {
        return sys_get_temp_dir() . '/crystal-rock-import-' . bin2hex(random_bytes(6)) . ".{$extension}";
    }
}
