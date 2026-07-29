'use strict';

const test = require('node:test');
const assert = require('node:assert/strict');
const presentation = require('../../public/assets/js/catalog-presentation.js');

test('resuelve la herencia de estilos por variante', () => {
    const state = presentation.createPresentationState();

    assert.equal(presentation.listRoles().length, 21);
    assert.equal(presentation.listTemplates().length, 9);
    assert.equal(presentation.resolveRoleStyle(state, 'grid4', 'productName').fontSize, '20px');
    assert.equal(presentation.resolveRoleStyle(state, 'grid1', 'productName').fontSize, '42px');
    assert.equal(presentation.resolveRoleStyle(state, 'coverMinimal', 'coverTitle').color, '#702d45');
});

test('mantiene aislados los ajustes entre variantes', () => {
    const state = presentation.createPresentationState();

    presentation.updateRoleStyle(state, 'grid4', 'productCode', {
        color: '#123456',
        fontSize: '15px',
    });

    assert.equal(presentation.resolveRoleStyle(state, 'grid4', 'productCode').color, '#123456');
    assert.equal(presentation.resolveRoleStyle(state, 'grid4', 'productCode').fontSize, '15px');
    assert.equal(presentation.resolveRoleStyle(state, 'grid2', 'productCode').color, '#702d45');
    assert.equal(presentation.resolveRoleStyle(state, 'grid2', 'productCode').fontSize, '16px');
});

test('restablece un rol sin modificar otros roles', () => {
    const state = presentation.createPresentationState();

    presentation.updateRoleStyle(state, 'featured', 'productPrice', { fontSize: '25px' });
    presentation.updateRoleStyle(state, 'featured', 'productCode', { fontSize: '20px' });
    presentation.resetRoleStyle(state, 'featured', 'productPrice');

    assert.equal(presentation.resolveRoleStyle(state, 'featured', 'productPrice').fontSize, '18px');
    assert.equal(presentation.resolveRoleStyle(state, 'featured', 'productCode').fontSize, '20px');
});

test('rechaza propiedades y roles no declarados', () => {
    const state = presentation.createPresentationState();

    assert.throws(
        () => presentation.updateRoleStyle(state, 'grid4', 'productCode', { transform: 'rotate(20deg)' }),
        /Propiedad no permitida/,
    );
    assert.throws(
        () => presentation.resolveRoleStyle(state, 'grid4', 'unknownRole'),
        /Rol visual desconocido/,
    );
});

test('cada estado de presentación es independiente', () => {
    const first = presentation.createPresentationState();
    const second = presentation.createPresentationState();

    first.theme.colors.wine = '#000000';

    assert.equal(first.theme.colors.wine, '#000000');
    assert.equal(second.theme.colors.wine, '#702d45');
});
