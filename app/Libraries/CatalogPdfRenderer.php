<?php

namespace App\Libraries;

use InvalidArgumentException;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

final class CatalogPdfRenderer
{
    private readonly string $assetRoot;

    private readonly string $tempDir;

    public function __construct(
        ?string $assetRoot = null,
        ?string $tempDir = null,
    ) {
        $publicRoot = defined('FCPATH') ? FCPATH : PUBLICPATH;
        $this->assetRoot = $assetRoot ?? $publicRoot . 'assets/img/catalog';
        $this->tempDir = $tempDir ?? WRITEPATH . 'cache/mpdf';
    }

    /**
     * @param array<string, mixed> $model
     */
    public function render(array $model): string
    {
        $this->assertValidModel($model);

        if (! is_dir($this->tempDir) && ! mkdir($concurrentDirectory = $this->tempDir, 0775, true) && ! is_dir($concurrentDirectory)) {
            throw new InvalidArgumentException('No fue posible preparar el directorio temporal del PDF.');
        }

        $mpdf = new Mpdf([
            'format' => $model['document']['format'],
            'orientation' => $model['document']['orientation'] === 'landscape' ? 'L' : 'P',
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'tempDir' => $this->tempDir,
        ]);
        $mpdf->SetTitle('Catálogo Crystal Rock · Prueba EV7');
        $mpdf->SetAuthor('Crystal Rock');
        $mpdf->SetCreator('Generador automático de catálogos');
        $mpdf->SetAutoPageBreak(false, 0);

        foreach ($model['pages'] as $page) {
            $mpdf->AddPage();
            $this->drawPage($mpdf, $page, $model['theme']);
        }

        return $mpdf->Output('', Destination::STRING_RETURN);
    }

    /**
     * @param array<string, mixed> $page
     * @param array<string, mixed> $theme
     */
    private function drawPage(Mpdf $mpdf, array $page, array $theme): void
    {
        $this->fillRect($mpdf, 0, 0, 210, 297, $theme['paper']);

        if ($page['template'] === 'cover') {
            $mpdf->Image($this->assetPath($page['backgroundImage']), 0, 0, 210, 297, '', '', true, false);
            $this->fillRect($mpdf, 0, 0, 210, 297, '#29332F', 0.38);
        } elseif ($page['template'] === 'featured') {
            $this->fillRect($mpdf, 0, 0, 89, 297, $theme['wineDark']);
            $this->roundedRect($mpdf, 11, 13, 78, 238, 5, $theme['white']);
            $this->fillRect($mpdf, 108, 218, 71, 1.2, $theme['terracotta']);
        } elseif ($page['template'] === 'grid4') {
            $this->fillRect($mpdf, 14, 38, 182, 0.8, $theme['terracotta']);
            foreach ($page['products'] as $product) {
                $this->drawProductCard($mpdf, $product, $theme);
            }
        }

        foreach ($page['elements'] as $element) {
            $element['kind'] === 'image'
                ? $this->drawImageElement($mpdf, $element)
                : $this->drawTextElement($mpdf, $element);
        }
    }

    /**
     * @param array<string, mixed> $product
     * @param array<string, mixed> $theme
     */
    private function drawProductCard(Mpdf $mpdf, array $product, array $theme): void
    {
        $x = (float) $product['x'];
        $y = (float) $product['y'];
        $this->roundedRect($mpdf, $x, $y, 88, 104, 4, '#FFFFFF');
        $this->roundedRect($mpdf, $x + 5, $y + 5, 78, 58, 3, $theme['paper']);
        $this->drawImageContain($mpdf, $this->assetPath($product['image']), $x + 7, $y + 7, 74, 54);

        $this->writeFixedText($mpdf, $product['name'], $x + 6, $y + 67, 76, 13, [
            'fontFamily' => $theme['fontSerif'],
            'fontSizePt' => 12,
            'fontWeight' => 700,
            'color' => $theme['ink'],
            'background' => null,
            'align' => 'left',
        ]);
        $this->writeFixedText($mpdf, $product['code'], $x + 6, $y + 83, 42, 9, [
            'fontFamily' => $theme['fontSans'],
            'fontSizePt' => 6.5,
            'fontWeight' => 700,
            'color' => $theme['wine'],
            'background' => null,
            'align' => 'left',
        ]);
        $this->writeFixedText($mpdf, $product['price'], $x + 51, $y + 80, 31, 13, [
            'fontFamily' => $theme['fontSans'],
            'fontSizePt' => 11,
            'fontWeight' => 700,
            'color' => '#FFFFFF',
            'background' => $theme['wine'],
            'align' => 'center',
        ]);
    }

    /**
     * @param array<string, mixed> $element
     */
    private function drawTextElement(Mpdf $mpdf, array $element): void
    {
        $frame = $element['frame'];
        $this->writeFixedText(
            $mpdf,
            $element['content'],
            $frame['x'],
            $frame['y'],
            $frame['width'],
            $frame['height'],
            $element['style'],
        );
    }

    /**
     * @param array<string, mixed> $style
     */
    private function writeFixedText(
        Mpdf $mpdf,
        string $content,
        float $x,
        float $y,
        float $width,
        float $height,
        array $style,
    ): void {
        if ($style['background'] !== null) {
            $this->roundedRect($mpdf, $x, $y, $width, $height, min(2.5, $height / 3), $style['background']);
        }
        $html = '<div style="box-sizing:border-box;width:100%;height:100%;padding:1.5mm 2mm;'
            . 'font-family:' . $style['fontFamily'] . ';font-size:' . $style['fontSizePt'] . 'pt;'
            . 'font-weight:' . $style['fontWeight'] . ';line-height:1.12;color:' . $style['color'] . ';'
            . 'background:transparent;text-align:' . $style['align'] . ';">'
            . nl2br($this->escape($content))
            . '</div>';

        $mpdf->WriteFixedPosHTML($html, $x, $y, $width, $height, 'hidden');
    }

    /**
     * @param array<string, mixed> $element
     */
    private function drawImageElement(Mpdf $mpdf, array $element): void
    {
        $frame = $element['frame'];
        $radius = (float) ($element['mask']['radiusMm'] ?? 0);
        $this->roundedRect($mpdf, $frame['x'], $frame['y'], $frame['width'], $frame['height'], $radius, '#FFFFFF');
        $this->drawImageContain(
            $mpdf,
            $this->assetPath($element['source']),
            $frame['x'] + 2,
            $frame['y'] + 2,
            $frame['width'] - 4,
            $frame['height'] - 4,
        );
    }

    private function drawImageContain(
        Mpdf $mpdf,
        string $path,
        float $x,
        float $y,
        float $width,
        float $height,
    ): void {
        $size = getimagesize($path);
        if ($size === false || $size[0] === 0 || $size[1] === 0) {
            throw new InvalidArgumentException('No fue posible calcular el tamaño de una imagen.');
        }

        $scale = min($width / $size[0], $height / $size[1]);
        $renderWidth = $size[0] * $scale;
        $renderHeight = $size[1] * $scale;
        $renderX = $x + (($width - $renderWidth) / 2);
        $renderY = $y + (($height - $renderHeight) / 2);
        $mpdf->Image($path, $renderX, $renderY, $renderWidth, $renderHeight, '', '', true, false);
    }

    private function fillRect(
        Mpdf $mpdf,
        float $x,
        float $y,
        float $width,
        float $height,
        string $color,
        float $opacity = 1,
    ): void {
        [$red, $green, $blue] = $this->rgb($color);
        if ($opacity < 1) {
            $mpdf->SetAlpha($opacity);
        }
        $mpdf->SetFillColor($red, $green, $blue);
        $mpdf->Rect($x, $y, $width, $height, 'F');
        if ($opacity < 1) {
            $mpdf->SetAlpha(1);
        }
    }

    private function roundedRect(
        Mpdf $mpdf,
        float $x,
        float $y,
        float $width,
        float $height,
        float $radius,
        string $color,
    ): void {
        [$red, $green, $blue] = $this->rgb($color);
        $mpdf->SetFillColor($red, $green, $blue);
        $mpdf->RoundedRect($x, $y, $width, $height, $radius, 'F');
    }

    /**
     * @return array{int, int, int}
     */
    private function rgb(string $color): array
    {
        $hex = ltrim($color, '#');
        if (strlen($hex) !== 6 || ! ctype_xdigit($hex)) {
            throw new InvalidArgumentException("Color hexadecimal no válido: {$color}");
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * @param array<string, mixed> $model
     */
    public function buildHtml(array $model): string
    {
        $this->assertValidModel($model);
        $theme = $model['theme'];
        $pages = [];

        foreach ($model['pages'] as $page) {
            $pages[] = match ($page['template']) {
                'cover' => $this->renderCover($page, $theme),
                'featured' => $this->renderFeatured($page, $theme),
                'grid4' => $this->renderGrid($page, $theme),
                default => throw new InvalidArgumentException('Plantilla PDF no soportada: ' . $page['template']),
            };
        }

        $css = $this->stylesheet($theme);

        return '<!doctype html><html lang="es"><head><meta charset="utf-8"><style>'
            . $css
            . '</style></head><body>'
            . implode('', $pages)
            . '</body></html>';
    }

    /**
     * @param array<string, mixed> $model
     */
    private function assertValidModel(array $model): void
    {
        foreach (['version', 'document', 'theme', 'pages'] as $required) {
            if (! array_key_exists($required, $model)) {
                throw new InvalidArgumentException("Falta la propiedad obligatoria '{$required}' en el contrato.");
            }
        }

        if ($model['version'] !== CatalogPresentationContract::VERSION) {
            throw new InvalidArgumentException('Versión de contrato no soportada.');
        }

        if (! is_array($model['pages']) || $model['pages'] === []) {
            throw new InvalidArgumentException('El catálogo debe contener al menos una página.');
        }
    }

    /**
     * @param array<string, mixed> $theme
     */
    private function stylesheet(array $theme): string
    {
        return '
            @page { size: A4 portrait; margin: 0; }
            html, body { margin: 0; padding: 0; font-family: ' . $theme['fontSans'] . '; color: ' . $theme['ink'] . '; }
            .pdf-page { position: relative; width: 210mm; height: 297mm; overflow: hidden; page-break-after: always; background: ' . $theme['paper'] . '; }
            .pdf-page:last-child { page-break-after: avoid; }
            .cover-photo { position: absolute; top: 0; left: 0; width: 210mm; height: 297mm; }
            .cover-overlay { position: absolute; top: 0; left: 0; width: 210mm; height: 297mm; background: rgba(41, 51, 47, .46); }
            .pdf-text { position: absolute; box-sizing: border-box; padding: 1.5mm 2mm; line-height: 1.12; overflow: hidden; }
            .pdf-image-frame { position: absolute; box-sizing: border-box; overflow: hidden; background: #FFFFFF; border: .45mm solid rgba(112,45,69,.12); }
            .pdf-image-frame img { width: 100%; height: 100%; }
            .feature-accent { position: absolute; top: 0; left: 0; width: 89mm; height: 297mm; background: ' . $theme['wineDark'] . '; }
            .feature-panel { position: absolute; top: 13mm; left: 11mm; width: 78mm; height: 238mm; background: ' . $theme['white'] . '; border-radius: 5mm; }
            .feature-mark { position: absolute; left: 108mm; top: 218mm; width: 71mm; height: 1.2mm; background: ' . $theme['terracotta'] . '; }
            .grid-rule { position: absolute; left: 14mm; top: 38mm; width: 182mm; height: .8mm; background: ' . $theme['terracotta'] . '; }
            .product-card { position: absolute; width: 88mm; height: 104mm; background: #FFFFFF; border: .35mm solid #E8DDD4; border-radius: 4mm; }
            .product-image { position: absolute; left: 5mm; top: 5mm; width: 78mm; height: 58mm; text-align: center; overflow: hidden; background: #FCF9F3; border-radius: 3mm; }
            .product-image img { height: 56mm; width: auto; }
            .product-name { position: absolute; left: 6mm; top: 67mm; width: 76mm; height: 13mm; color: ' . $theme['ink'] . '; font-family: ' . $theme['fontSerif'] . '; font-size: 12pt; font-weight: bold; }
            .product-code { position: absolute; left: 6mm; top: 83mm; width: 42mm; height: 9mm; color: ' . $theme['wine'] . '; font-size: 6.5pt; font-weight: bold; }
            .product-price { position: absolute; right: 5mm; top: 80mm; min-width: 31mm; height: 13mm; padding: 2.3mm 3mm; color: #FFFFFF; background: ' . $theme['wine'] . '; border-radius: 2.5mm; text-align: center; font-size: 11pt; font-weight: bold; }
        ';
    }

    /**
     * @param array<string, mixed> $page
     * @param array<string, mixed> $theme
     */
    private function renderCover(array $page, array $theme): string
    {
        return '<section class="pdf-page">'
            . '<img class="cover-photo" src="' . $this->asset($page['backgroundImage']) . '">'
            . '<div class="cover-overlay"></div>'
            . $this->renderElements($page['elements'])
            . '</section>';
    }

    /**
     * @param array<string, mixed> $page
     * @param array<string, mixed> $theme
     */
    private function renderFeatured(array $page, array $theme): string
    {
        return '<section class="pdf-page">'
            . '<div class="feature-accent"></div><div class="feature-panel"></div><div class="feature-mark"></div>'
            . $this->renderElements($page['elements'])
            . '</section>';
    }

    /**
     * @param array<string, mixed> $page
     * @param array<string, mixed> $theme
     */
    private function renderGrid(array $page, array $theme): string
    {
        $cards = '';
        foreach ($page['products'] as $product) {
            $cards .= '<div class="product-card" style="left:' . $product['x'] . 'mm;top:' . $product['y'] . 'mm">'
                . '<div class="product-image"><img src="' . $this->asset($product['image']) . '"></div>'
                . '<div class="product-name">' . $this->escape($product['name']) . '</div>'
                . '<div class="product-code">' . $this->escape($product['code']) . '</div>'
                . '<div class="product-price">' . $this->escape($product['price']) . '</div>'
                . '</div>';
        }

        return '<section class="pdf-page"><div class="grid-rule"></div>'
            . $this->renderElements($page['elements'])
            . $cards
            . '</section>';
    }

    /**
     * @param array<int, array<string, mixed>> $elements
     */
    private function renderElements(array $elements): string
    {
        $html = '';
        foreach ($elements as $element) {
            $frame = $element['frame'];
            $frameCss = 'left:' . $frame['x'] . 'mm;top:' . $frame['y'] . 'mm;width:' . $frame['width'] . 'mm;height:' . $frame['height'] . 'mm;';

            if ($element['kind'] === 'image') {
                $radius = $element['mask']['radiusMm'] ?? 0;
                $html .= '<div class="pdf-image-frame" style="' . $frameCss . 'border-radius:' . $radius . 'mm">'
                    . '<img src="' . $this->asset($element['source']) . '"></div>';
                continue;
            }

            $style = $element['style'];
            $background = $style['background'] === null ? 'transparent' : $style['background'];
            $html .= '<div class="pdf-text" data-role="' . $this->escape($element['role']) . '" style="'
                . $frameCss
                . 'font-family:' . $style['fontFamily'] . ';'
                . 'font-size:' . $style['fontSizePt'] . 'pt;'
                . 'font-weight:' . $style['fontWeight'] . ';'
                . 'color:' . $style['color'] . ';'
                . 'background:' . $background . ';'
                . 'text-align:' . $style['align'] . ';">'
                . nl2br($this->escape($element['content']))
                . '</div>';
        }

        return $html;
    }

    private function asset(string $filename): string
    {
        return 'file://' . str_replace('%2F', '/', rawurlencode($this->assetPath($filename)));
    }

    private function assetPath(string $filename): string
    {
        $path = realpath($this->assetRoot . DIRECTORY_SEPARATOR . basename($filename));
        if ($path === false) {
            throw new InvalidArgumentException("No existe la imagen '{$filename}'.");
        }

        return $path;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
