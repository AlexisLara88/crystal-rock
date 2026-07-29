const assert = require('node:assert/strict');
const test = require('node:test');

const composer = require('../../public/assets/js/catalog-composer.js');

const product = (sourceRow, category) => ({
    sourceRow,
    name: `Producto ${sourceRow}`,
    code: `P-${sourceRow}`,
    price: '$10.00',
    category,
    measurements: '10 cm',
    material: 'Vidrio',
    packaging: 'Caja',
    pack: '6u',
    master: '24u',
    image: { preview: `data:image/jpeg;base64,${sourceRow}` },
});

test('compone portada, categorías y contraportada en orden', () => {
    const pages = composer.composeCatalog({
        categories: [{ name: 'Copas' }, { name: 'Accesorios' }],
        products: [
            product(2, 'Copas'),
            product(3, 'Copas'),
            product(4, 'Accesorios'),
        ],
    }, {
        generatedAt: new Date('2026-07-29T12:00:00Z'),
    });

    assert.deepEqual(pages.map(page => page.type), [
        'cover',
        'featured',
        'grid',
        'featured',
        'back',
    ]);
    assert.equal(pages[1].product.sourceRow, 2);
    assert.deepEqual(pages[2].products.map(item => item.sourceRow), [3]);
    assert.equal(pages[3].product.sourceRow, 4);
    assert.match(pages[0].updatedLabel, /^ACTUALIZADO:/);
});

test('excluye el destacado y pagina el resto en bloques de hasta cuatro', () => {
    const pages = composer.composeCatalog({
        categories: [{ name: 'Copas' }],
        products: Array.from({ length: 10 }, (_, index) => product(index + 2, 'Copas')),
    });
    const grids = pages.filter(page => page.type === 'grid');

    assert.deepEqual(grids.map(page => page.count), [4, 4, 1]);
    assert.equal(grids.flatMap(page => page.products).some(item => item.sourceRow === 2), false);
});

test('normaliza especificaciones e imagen para las plantillas', () => {
    const normalized = composer.normalizeProduct({
        ...product(2, 'Copas'),
        packaging: '',
    });

    assert.equal(normalized.editorKey, 'row-2');
    assert.equal(normalized.imageSource, 'data:image/jpeg;base64,2');
    assert.deepEqual(normalized.specs[0], ['Medidas', '10 cm']);
    assert.equal(normalized.specs.some(([label]) => label === 'Embalaje'), false);
});

test('rechaza modelos vacíos', () => {
    assert.throws(
        () => composer.composeCatalog({ categories: [], products: [] }),
        /No hay productos/,
    );
});
