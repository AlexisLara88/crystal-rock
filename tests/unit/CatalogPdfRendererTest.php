<?php

use App\Libraries\CatalogPdfRenderer;
use App\Libraries\CatalogPresentationContract;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class CatalogPdfRendererTest extends CIUnitTestCase
{
    public function testBuildHtmlTranslatesSemanticRolesAndMillimetres(): void
    {
        $renderer = new CatalogPdfRenderer();
        $html = $renderer->buildHtml(CatalogPresentationContract::proof());

        $this->assertStringContainsString('data-role="productName"', $html);
        $this->assertStringContainsString('data-role="productCode"', $html);
        $this->assertStringContainsString('data-role="price"', $html);
        $this->assertStringContainsString('left:108mm;top:177mm;width:73mm;height:22mm;', $html);
        $this->assertSame(3, substr_count($html, 'class="pdf-page"'));
    }

    public function testRenderReturnsARealPdfDocument(): void
    {
        $renderer = new CatalogPdfRenderer();
        $pdf = $renderer->render(CatalogPresentationContract::proof());

        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertGreaterThan(10_000, strlen($pdf));
        $this->assertLessThan(3_000_000, strlen($pdf));
    }

    public function testRejectsUnknownContractVersion(): void
    {
        $model = CatalogPresentationContract::proof();
        $model['version'] = '99';

        $this->expectException(\InvalidArgumentException::class);
        (new CatalogPdfRenderer())->buildHtml($model);
    }
}
