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
- interfaz de estudio oscura y consistente entre navegadores, separada de la apariencia real de las hojas A4.
- selección de texto neutra: un clic no altera dimensiones ni posición.
- historial de sesión con deshacer/rehacer, restablecimientos amplios y advertencias visuales.
- contrato de presentación versionado y prueba técnica mPDF A4.
- descarga de las siete hojas que el editor está mostrando, con los ajustes actuales de texto e imagen.

Los incrementos operativos de D2 agregan:

- carga local de archivos `.xlsx` y `.csv` de hasta 5 MB;
- detección de hoja, encabezados y aliases de columnas;
- conservación de códigos como texto y precios con su formato visible;
- validación inicial de obligatorios, precios, categorías y códigos repetidos;
- resumen de importación y revisión por fila antes de componer;
- carga de un ZIP o hasta 20 imágenes JPG, PNG y WebP;
- asociación por nombre o ruta declarada en `@Image`;
- miniaturas y detección de imágenes faltantes, duplicadas, sobrantes, inválidas o de baja resolución;
- resolución por fila mediante reemplazo manual, imagen genérica o exclusión reversible del producto;
- edición de nombre, código y precio durante la sesión;
- categorías sugeridas, creación de categorías y asignación masiva;
- asignación rápida al tocar una categoría y creación con asignación inmediata a las filas seleccionadas;
- validación dinámica de obligatorios y códigos repetidos;
- confirmación de un modelo normalizado listo para la composición.

Esta revisión todavía no reemplaza los datos de las plantillas. El modelo confirmado queda preparado para el siguiente incremento de D3, que construirá y paginará el catálogo automáticamente.

La descarga visible genera una copia gráfica A4 fiel al estado de la sesión; la ruta mPDF permanece como prueba técnica interna hasta que EV8 conecte el modelo normalizado y los productos importados.

## Stack

- PHP 8.2+
- CodeIgniter 4
- Vue 3 mediante CDN
- CSS propio para las composiciones A4
- html2canvas 1.4 y jsPDF 4.2 para la exportación visual de la demo
- PhpSpreadsheet 5.9 para lectura de Excel y CSV
- MariaDB/MySQL previsto para el producto completo
- mPDF 8.3 previsto como motor estructurado del producto

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
/demo/catalogo/importar
/demo/catalogo/imagenes
/demo/catalogo/pdf-prueba
```

Las rutas `importar` e `imagenes` reciben solicitudes `POST` con protección CSRF. La segunda procesa los archivos solo para la revisión actual y no los conserva como biblioteca persistente.

### Paquete de prueba de imágenes

`public/downloads/crystal-rock-imagenes-prueba.zip` contiene ocho imágenes sintéticas en formato JPG, nombradas `1.jpg` a `8.jpg` para coincidir directamente con la columna `@Image` de `Final_Secundario.xlsx`.

El paquete permite probar el ciclo de carga, asociación, revisión y confirmación sin reutilizar las imágenes visibles en las plantillas. Es material temporal de demostración y no un activo comercial oficial de Crystal Rock.

## Verificaciones

```bash
/opt/lampp/bin/php vendor/bin/phpunit
/opt/lampp/bin/php -l app/Views/catalog_demo.php
node --check public/assets/js/catalog-demo.js
node --check public/assets/js/catalog-pdf-export.js
node --check public/assets/js/catalog-presentation.js
node --test tests/js/catalog-presentation.test.js tests/js/catalog-pdf-export.test.js tests/js/catalog-import-review.test.js
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
