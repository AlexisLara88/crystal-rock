<?php

use App\Libraries\CatalogImageMatcher;
use CodeIgniter\Test\CIUnitTestCase;

final class CatalogImageMatcherTest extends CIUnitTestCase
{
    public function testMatchesImagesAndReportsMissingDuplicatesWarningsAndUnusedFiles(): void
    {
        $matcher = new CatalogImageMatcher();
        $large = $this->png(1000, 1000);
        $small = $this->png(160, 160);

        $result = $matcher->match(
            [
                ['sourceRow' => 2, 'image' => 'HERO.PNG'],
                ['sourceRow' => 3, 'image' => 'missing.png'],
                ['sourceRow' => 4, 'image' => 'duplicate.png'],
                ['sourceRow' => 5, 'image' => 'small.png'],
                ['sourceRow' => 6, 'image' => 'hero.png'],
            ],
            [
                ['name' => 'hero.png', 'content' => $large],
                ['name' => 'one/duplicate.png', 'content' => $large],
                ['name' => 'two/duplicate.png', 'content' => $large],
                ['name' => 'small.png', 'content' => $small],
                ['name' => 'unused.png', 'content' => $large],
                ['name' => 'notes.txt', 'content' => 'not an image'],
            ],
        );

        $this->assertSame(6, $result['summary']['receivedFiles']);
        $this->assertSame(5, $result['summary']['usableFiles']);
        $this->assertSame(3, $result['summary']['matchedRows']);
        $this->assertSame(3, $result['summary']['warningRows']);
        $this->assertSame(1, $result['summary']['missingRows']);
        $this->assertSame(1, $result['summary']['duplicateRows']);
        $this->assertSame(3, $result['summary']['unusedFiles']);
        $this->assertSame(1, $result['summary']['rejectedFiles']);
        $this->assertSame('warning', $result['rows'][0]['status']);
        $this->assertStringStartsWith('data:image/jpeg;base64,', $result['rows'][0]['image']['preview']);
        $this->assertSame('missing', $result['rows'][1]['status']);
        $this->assertSame('duplicate', $result['rows'][2]['status']);
        $this->assertCount(2, $result['rows'][2]['candidates']);
        $this->assertStringContainsString('Resolución baja', $result['rows'][3]['warnings'][0]);
    }

    public function testReadsZipWithoutExtractingUnsafePaths(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'catalog-images-');
        $this->assertNotFalse($path);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path, ZipArchive::OVERWRITE) === true);
        $zip->addFromString('products/hero.png', $this->png(900, 900));
        $zip->addFromString('docs/readme.txt', 'reference');
        $zip->addFromString('../escape.png', $this->png(900, 900));
        $zip->close();

        try {
            $files = (new CatalogImageMatcher())->readZip($path);
        } finally {
            unlink($path);
        }

        $this->assertCount(2, $files);
        $this->assertSame('products/hero.png', $files[0]['name']);
        $this->assertSame('docs/readme.txt', $files[1]['name']);
    }

    private function png(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        $background = imagecolorallocate($image, 112, 45, 69);
        imagefill($image, 0, 0, $background);

        ob_start();
        imagepng($image);
        $content = (string) ob_get_clean();
        imagedestroy($image);

        return $content;
    }
}
