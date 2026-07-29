const test = require('node:test');
const assert = require('node:assert/strict');

const reviewEngine = require('../../public/assets/js/catalog-import-review.js');

const rows = [
    { sourceRow: 2, values: { material: 'Vidrio', image: '1.jpg' } },
    { sourceRow: 3, values: { material: 'Acero', image: '2.jpg' } },
    { sourceRow: 4, values: { material: 'Acero', image: '3.jpg' } },
];

test('normaliza y formatea los precios del catálogo', () => {
    assert.equal(reviewEngine.parsePrice('$3,852.33'), 3852.33);
    assert.equal(reviewEngine.parsePrice('3852,33'), 3852.33);
    assert.equal(reviewEngine.parsePrice('sin precio'), null);
    assert.equal(reviewEngine.formatPrice('3852,3'), '$3,852.30');
});

test('bloquea obligatorios y códigos repetidos dentro de una categoría', () => {
    const result = reviewEngine.reviewRows({
        rows,
        editsByRow: {
            2: { name: 'Copa', code: 'A-1', price: '$10.00', category: 'Copas' },
            3: { name: 'Sacacorcho', code: 'B-1', price: '$20.00', category: 'Accesorios' },
            4: { name: 'Otro', code: 'B-1', price: '$30.00', category: 'Accesorios' },
        },
        excludedRows: {},
    });

    assert.equal(result.summary.errorRows, 2);
    assert.match(result.byRow['3'].errors[0], /misma categoría/);
    assert.match(result.byRow['4'].errors[0], /misma categoría/);
});

test('permite el mismo código en categorías diferentes con advertencia', () => {
    const result = reviewEngine.reviewRows({
        rows: rows.slice(0, 2),
        editsByRow: {
            2: { name: 'Copa', code: 'A-1', price: '$10.00', category: 'Copas' },
            3: { name: 'Sacacorcho', code: 'A-1', price: '$20.00', category: 'Accesorios' },
        },
        excludedRows: {},
    });

    assert.equal(result.summary.errorRows, 0);
    assert.equal(result.summary.warningRows, 2);
});

test('construye el modelo únicamente con filas activas e imágenes resueltas', () => {
    const model = reviewEngine.buildApprovedModel({
        rows,
        editsByRow: {
            2: { name: 'Copa', code: 'A-1', price: '10', category: 'Copas' },
            3: { name: 'Sacacorcho', code: 'B-1', price: '20', category: 'Accesorios' },
            4: { name: 'Excluir', code: 'C-1', price: '30', category: 'Accesorios' },
        },
        excludedRows: { 4: true },
        imageMatchesByRow: {
            2: { status: 'matched', image: { name: '1.jpg' } },
            3: { status: 'generic', image: { name: 'Imagen genérica' } },
        },
        categories: ['Copas', 'Accesorios', 'Sin uso'],
    });

    assert.equal(model.products.length, 2);
    assert.deepEqual(model.categories.map(category => category.name), ['Copas', 'Accesorios']);
    assert.equal(model.products[0].price, '$10.00');
    assert.equal(model.products[1].imageDecision, 'generic');
});
