<?php

namespace App\Libraries;

final class CatalogPresentationContract
{
    public const VERSION = '1.0';

    /**
     * Modelo mínimo que comparten el editor visual y el renderizador PDF.
     *
     * Las coordenadas y dimensiones se expresan en milímetros para evitar
     * depender del tamaño de pantalla o de reglas CSS exclusivas del navegador.
     *
     * @return array<string, mixed>
     */
    public static function proof(): array
    {
        return [
            'version' => self::VERSION,
            'document' => [
                'format' => 'A4',
                'orientation' => 'portrait',
                'widthMm' => 210,
                'heightMm' => 297,
                'intent' => 'digital',
            ],
            'theme' => [
                'paper' => '#FCF9F3',
                'ink' => '#29332F',
                'wine' => '#702D45',
                'wineDark' => '#4E1F31',
                'terracotta' => '#C9795B',
                'sage' => '#8C9B85',
                'white' => '#FFFFFF',
                'fontSans' => 'dejavusans',
                'fontSerif' => 'dejavuserif',
            ],
            'pages' => [
                [
                    'id' => 'cover-editorial',
                    'template' => 'cover',
                    'variant' => 'editorial',
                    'backgroundImage' => 'cover-wine.png',
                    'elements' => [
                        self::text('promotionText', 'Hacé tu compra por la web y obtené un', 16, 18, 61, 20, 9, '#FFFFFF', 600),
                        self::text('promotionValue', '10% OFF', 16, 39, 70, 20, 22, '#FFFFFF', 800),
                        self::text('wordmark', 'CRYSTALROCK', 16, 220, 88, 10, 8, '#FFFFFF', 700),
                        self::text('coverTitle', 'Cristalería', 16, 234, 135, 25, 25, '#FFFFFF', 700, 'dejavuserif'),
                        self::text('coverSubtitle', 'Calidad real para casas reales', 17, 263, 118, 10, 8, '#FFFFFF', 400),
                        self::text('date', 'ACTUALIZADO: 28/07/26', 151, 15, 43, 9, 6, '#702D45', 700, 'dejavusans', '#FCF9F3', 'center'),
                    ],
                ],
                [
                    'id' => 'featured-product',
                    'template' => 'featured',
                    'elements' => [
                        self::text('category', 'CRISTALERÍA', 13, 14, 55, 9, 7, '#FFFFFF', 700, 'dejavusans', '#702D45', 'center'),
                        self::text('featuredLabel', "PRODUCTO\nDESTACADO", 140, 16, 53, 18, 10, '#702D45', 800),
                        self::text('productName', 'Copas Gin Tonic 590 ML', 106, 64, 88, 24, 14, '#29332F', 700, 'dejavuserif'),
                        self::image('productImage', 'glass-gin.png', 22, 75, 74, 121, 5),
                        self::text('productSpecs', "Capacidad 590 ML\nMaterial cristal\nPresentación caja x 6", 108, 101, 78, 38, 7, '#29332F', 400),
                        self::text('productCode', 'CÓD. CR-590-GT', 108, 157, 64, 13, 7, '#702D45', 700, 'dejavusans', '#F5ECDE'),
                        self::text('price', '$3,852.33', 108, 177, 73, 22, 16, '#FFFFFF', 800, 'dejavusans', '#702D45', 'center'),
                        self::text('footer', 'Crystal Rock · Catálogo digital', 13, 277, 184, 8, 6, '#586A5B', 400, 'dejavusans', null, 'right'),
                    ],
                ],
                [
                    'id' => 'product-grid',
                    'template' => 'grid4',
                    'elements' => [
                        self::text('sectionTitle', 'Copas & cristalería', 14, 15, 150, 18, 16, '#702D45', 700, 'dejavuserif'),
                    ],
                    'products' => [
                        self::product('Copa Premium', 'CR-PREM-01', '$2,490.00', 'glass-premium.png', 14, 48),
                        self::product('Copa de Vino', 'CR-WINE-02', '$1,980.00', 'glass-wine.png', 108, 48),
                        self::product('Copa de Cata', 'CR-TAST-03', '$2,120.00', 'glass-tasting.png', 14, 160),
                        self::product('Copa Gin Tonic', 'CR-GIN-04', '$3,852.33', 'glass-gin.png', 108, 160),
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function text(
        string $role,
        string $content,
        float $x,
        float $y,
        float $width,
        float $height,
        float $fontSizePt,
        string $color,
        int $fontWeight,
        string $fontFamily = 'dejavusans',
        ?string $background = null,
        string $align = 'left',
    ): array {
        return [
            'kind' => 'text',
            'role' => $role,
            'content' => $content,
            'frame' => compact('x', 'y', 'width', 'height'),
            'style' => compact('fontSizePt', 'color', 'fontWeight', 'fontFamily', 'background', 'align'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function image(
        string $role,
        string $source,
        float $x,
        float $y,
        float $width,
        float $height,
        float $radiusMm,
    ): array {
        return [
            'kind' => 'image',
            'role' => $role,
            'source' => $source,
            'frame' => compact('x', 'y', 'width', 'height'),
            'mask' => ['radiusMm' => $radiusMm],
            'adjustment' => ['zoom' => 1, 'offsetX' => 0, 'offsetY' => 0, 'rotation' => 0],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function product(
        string $name,
        string $code,
        string $price,
        string $image,
        float $x,
        float $y,
    ): array {
        return compact('name', 'code', 'price', 'image', 'x', 'y');
    }
}
