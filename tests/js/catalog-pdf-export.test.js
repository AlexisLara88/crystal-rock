const assert = require('node:assert/strict');
const test = require('node:test');

const exporter = require('../../public/assets/js/catalog-pdf-export.js');

test('ensambla siete capturas como siete páginas A4 en el orden recibido', async () => {
    const sheets = Array.from({ length: 7 }, (_, index) => ({ id: `page-${index + 1}` }));
    const captured = [];
    const progress = [];

    class FakePdf {
        constructor(options) {
            this.options = options;
            this.addedPages = [];
            this.addedImages = [];
            FakePdf.instance = this;
        }

        addPage(format, orientation) {
            this.addedPages.push([format, orientation]);
        }

        addImage(...args) {
            this.addedImages.push(args);
        }

        save(filename) {
            this.savedAs = filename;
        }
    }

    const result = await exporter.exportSheets({
        sheets,
        PdfConstructor: FakePdf,
        capture: async (sheet, options) => {
            captured.push([sheet.id, options]);

            return {
                toDataURL: (type, quality) => `${type}:${quality}:${sheet.id}`,
            };
        },
        onProgress: (current, total) => progress.push([current, total]),
    });

    assert.deepEqual(FakePdf.instance.options, {
        orientation: 'portrait',
        unit: 'mm',
        format: 'a4',
        compress: true,
    });
    assert.equal(captured.length, 7);
    assert.equal(FakePdf.instance.addedPages.length, 6);
    assert.equal(FakePdf.instance.addedImages.length, 7);
    assert.deepEqual(
        FakePdf.instance.addedImages.map((args) => args.slice(1, 8)),
        Array.from({ length: 7 }, (_, index) => [
            'JPEG',
            0,
            0,
            210,
            297,
            `catalog-page-${index + 1}`,
            'FAST',
        ]),
    );
    assert.equal(captured[0][1].width, 595);
    assert.equal(captured[0][1].height, 842);
    assert.equal(captured[0][1].scale, 2);
    assert.deepEqual(progress, Array.from({ length: 7 }, (_, index) => [index + 1, 7]));
    assert.equal(FakePdf.instance.savedAs, 'crystal-rock-catalogo-actual.pdf');
    assert.deepEqual(result, {
        filename: 'crystal-rock-catalogo-actual.pdf',
        pageCount: 7,
    });
});

test('rechaza una exportación sin hojas', async () => {
    await assert.rejects(
        exporter.exportSheets({
            sheets: [],
            capture: async () => ({}),
            PdfConstructor: class {},
        }),
        /No hay páginas/,
    );
});
