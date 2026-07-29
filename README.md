# Crystal Rock · Generador de catálogos

Demo web para automatizar catálogos comerciales PDF a partir de datos estructurados e imágenes de producto.

## Estado

La fase visual D1 incluye una muestra navegable con datos fijos:

- portada con tres tratamientos;
- apertura de categoría y producto destacado;
- grillas para uno, dos, tres y cuatro productos;
- contraportada comercial;
- previsualización A4 individual o de catálogo completo.
- editor contextual de imágenes;
- modelo de presentación separado por tema, plantilla y rol visual.
- selección contextual de todos los roles de texto.
- edición por variante de tipografía, tamaño, peso, color, fondo y alineación.
- arrastre y ajuste fino de posición, ancho y alto con límites de zona y bloqueo de colisiones.
- matriz declarativa de compatibilidad entre los 21 roles visuales y las nueve variantes.
- paleta flotante amplia, legible y reposicionable, independiente del elemento seleccionado.
- selección de texto neutra: un clic no altera dimensiones ni posición.

La importación de Excel, validaciones y generación PDF pertenecen a las siguientes fases.

## Stack

- PHP 8.2+
- CodeIgniter 4
- Vue 3 mediante CDN
- CSS propio para las composiciones A4
- MariaDB/MySQL previsto para el producto completo
- mPDF previsto para la generación PDF

## Inicio local

```bash
composer install
cp env .env
/opt/lampp/bin/php spark serve
```

Ajustar `app.baseURL` en `.env` al entorno local.

Rutas disponibles:

```text
/
/demo/catalogo
```

## Verificaciones

```bash
/opt/lampp/bin/php vendor/bin/phpunit
/opt/lampp/bin/php -l app/Views/catalog_demo.php
node --check public/assets/js/catalog-demo.js
node --check public/assets/js/catalog-presentation.js
node tests/js/catalog-presentation.test.js
composer audit --locked
```

## Archivos locales excluidos

El repositorio no incluye:

- `.env`;
- `vendor/`;
- archivos temporales de `writable/`;
- respaldos de base de datos;
- documentación operativa externa;
- metadatos locales de agentes.

Las imágenes incluidas en la fase visual son activos temporales de demostración y deberán reemplazarse por material oficial antes de uso comercial.
