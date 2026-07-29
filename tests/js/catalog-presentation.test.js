'use strict';

const test = require('node:test');
const assert = require('node:assert/strict');
const presentation = require('../../public/assets/js/catalog-presentation.js');

test('resuelve la herencia de estilos por variante', () => {
    const state = presentation.createPresentationState();

    assert.equal(presentation.listRoles().length, 21);
    assert.equal(presentation.listTemplates().length, 9);
    assert.equal(presentation.resolveRoleStyle(state, 'grid4', 'productName').fontSize, '20px');
    assert.equal(presentation.resolveRoleStyle(state, 'grid1', 'productName').fontSize, '30px');
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
    assert.equal(presentation.resolveRoleStyle(state, 'grid2', 'productCode').fontSize, '13px');
});

test('mantiene una escala tipográfica natural en las grillas de pocos productos', () => {
    const state = presentation.createPresentationState();

    assert.equal(presentation.resolveRoleStyle(state, 'grid3', 'productName').fontSize, '21px');
    assert.equal(presentation.resolveRoleStyle(state, 'grid2', 'productName').fontSize, '23px');
    assert.equal(presentation.resolveRoleStyle(state, 'grid1', 'productName').fontSize, '30px');
    assert.equal(presentation.resolveRoleStyle(state, 'grid1', 'productSpecs').fontSize, '16px');
    assert.equal(presentation.resolveRoleStyle(state, 'grid1', 'productPrice').fontSize, '18px');
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
    assert.throws(
        () => presentation.updateRoleStyle(state, 'grid4', 'productCode', { fontSize: '100px' }),
        /Tamaño fuera del rango/,
    );
    assert.throws(
        () => presentation.updateRoleStyle(state, 'grid4', 'productCode', { fontFamily: 'Comic Sans MS' }),
        /Tipografía no permitida/,
    );
});

test('acepta tipografía lógica, color personalizado y alineación por variante', () => {
    const state = presentation.createPresentationState();

    presentation.updateRoleStyle(state, 'grid3', 'productPrice', {
        fontFamily: 'font:serifAccent',
        color: '#123abc',
        backgroundColor: 'transparent',
        textAlign: 'right',
    });

    const style = presentation.resolveRoleStyle(state, 'grid3', 'productPrice');

    assert.match(style.fontFamily, /Georgia/);
    assert.equal(style.color, '#123abc');
    assert.equal(style.backgroundColor, 'transparent');
    assert.equal(style.textAlign, 'right');
});

test('cada estado de presentación es independiente', () => {
    const first = presentation.createPresentationState();
    const second = presentation.createPresentationState();

    first.theme.colors.wine = '#000000';

    assert.equal(first.theme.colors.wine, '#000000');
    assert.equal(second.theme.colors.wine, '#702d45');
});

test('acepta dimensiones y desplazamientos controlados por variante', () => {
    const state = presentation.createPresentationState();

    presentation.updateRoleStyle(state, 'grid2', 'productName', {
        baseWidth: 320,
        baseHeight: 74,
        widthScale: 0.75,
        heightScale: 0.6,
        offsetX: -18,
        offsetY: 24,
    });

    const style = presentation.resolveRoleStyle(state, 'grid2', 'productName');

    assert.equal(style.baseWidth, 320);
    assert.equal(style.baseHeight, 74);
    assert.equal(style.widthScale, 0.75);
    assert.equal(style.heightScale, 0.6);
    assert.equal(style.offsetX, -18);
    assert.equal(style.offsetY, 24);
});

test('rechaza dimensiones o desplazamientos inválidos', () => {
    const state = presentation.createPresentationState();

    assert.throws(
        () => presentation.updateRoleStyle(state, 'grid4', 'productPrice', { widthScale: 0.59 }),
        /Dimensión fuera del rango/,
    );
    assert.throws(
        () => presentation.updateRoleStyle(state, 'grid4', 'productPrice', { heightScale: 1.01 }),
        /Dimensión fuera del rango/,
    );
    assert.throws(
        () => presentation.updateRoleStyle(state, 'grid4', 'productPrice', { baseWidth: 0 }),
        /Dimensión base inválida/,
    );
    assert.throws(
        () => presentation.updateRoleStyle(state, 'grid4', 'productPrice', { offsetX: Number.NaN }),
        /Desplazamiento inválido/,
    );
});

test('restablece también los ajustes geométricos del rol', () => {
    const state = presentation.createPresentationState();

    presentation.updateRoleStyle(state, 'featured', 'category', {
        baseWidth: 280,
        baseHeight: 75,
        widthScale: 0.8,
        offsetX: 15,
    });
    presentation.resetRoleStyle(state, 'featured', 'category');

    const style = presentation.resolveRoleStyle(state, 'featured', 'category');

    assert.equal(style.baseWidth, undefined);
    assert.equal(style.widthScale, undefined);
    assert.equal(style.offsetX, undefined);
});

test('declara una matriz completa y válida de roles por plantilla', () => {
    const state = presentation.createPresentationState();
    const coveredRoles = new Set();
    let assignments = 0;

    for (const templateId of presentation.listTemplates()) {
        const roles = presentation.listTemplateRoles(templateId);

        assert.ok(roles.length > 0, `${templateId} debe declarar al menos un rol`);
        assert.equal(new Set(roles).size, roles.length, `${templateId} no debe repetir roles`);

        for (const role of roles) {
            coveredRoles.add(role);
            assignments += 1;
            assert.equal(presentation.isRoleSupported(templateId, role), true);
            assert.doesNotThrow(() => presentation.resolveRoleStyle(state, templateId, role));
        }
    }

    assert.equal(assignments, 56);
    assert.deepEqual([...coveredRoles].sort(), presentation.listRoles().sort());
});

test('rechaza roles conocidos que no pertenecen a una plantilla', () => {
    const state = presentation.createPresentationState();

    assert.equal(presentation.isRoleSupported('grid4', 'coverTitle'), false);
    assert.throws(
        () => presentation.resolveRoleStyle(state, 'grid4', 'coverTitle'),
        /no pertenece a la plantilla/,
    );
    assert.throws(
        () => presentation.listTemplateRoles('unknownTemplate'),
        /Plantilla desconocida/,
    );
});

test('cada rol publica límites geométricos y una zona comprensible', () => {
    for (const role of presentation.listRoles()) {
        const definition = presentation.getRoleDefinition(role);

        assert.ok(definition.zoneLabel.length > 0);
        assert.equal(definition.layout.minWidthScale, 0.6);
        assert.equal(definition.layout.maxWidthScale, 1);
        assert.equal(definition.layout.minHeightScale, 0.6);
        assert.equal(definition.layout.maxHeightScale, 1);
        assert.equal(definition.layout.nudgeStep, 4);
        assert.equal(definition.layout.movement, 'both');
    }
});

test('restaura exactamente un override previo después de un ajuste inválido', () => {
    const state = presentation.createPresentationState();

    presentation.updateRoleStyle(state, 'coverEditorial', 'promotionValue', {
        color: '#123456',
        fontWeight: 700,
    });
    const snapshot = presentation.getRoleOverride(state, 'coverEditorial', 'promotionValue');

    presentation.updateRoleStyle(state, 'coverEditorial', 'promotionValue', {
        baseWidth: 120,
        baseHeight: 40,
        widthScale: 0.6,
        offsetY: 18,
    });
    presentation.replaceRoleOverride(state, 'coverEditorial', 'promotionValue', snapshot);

    const style = presentation.resolveRoleStyle(state, 'coverEditorial', 'promotionValue');

    assert.equal(style.color, '#123456');
    assert.equal(style.fontWeight, 700);
    assert.equal(style.baseWidth, undefined);
    assert.equal(style.offsetY, undefined);
});

test('puede restaurar la ausencia total de override', () => {
    const state = presentation.createPresentationState();

    assert.equal(presentation.getRoleOverride(state, 'coverEditorial', 'promotionText'), null);
    presentation.updateRoleStyle(state, 'coverEditorial', 'promotionText', { offsetX: 9 });
    presentation.replaceRoleOverride(state, 'coverEditorial', 'promotionText', null);

    assert.equal(
        presentation.resolveRoleStyle(state, 'coverEditorial', 'promotionText').offsetX,
        undefined,
    );
});

test('restablece todos los roles de una variante sin afectar las demás', () => {
    const state = presentation.createPresentationState();

    presentation.updateRoleStyle(state, 'grid4', 'productName', { color: '#112233' });
    presentation.updateRoleStyle(state, 'grid4', 'productPrice', { fontWeight: 300 });
    presentation.updateRoleStyle(state, 'grid3', 'productName', { color: '#445566' });
    presentation.resetTemplateStyles(state, 'grid4');

    assert.equal(presentation.resolveRoleStyle(state, 'grid4', 'productName').color, '#702d45');
    assert.equal(presentation.resolveRoleStyle(state, 'grid4', 'productPrice').fontWeight, 800);
    assert.equal(presentation.resolveRoleStyle(state, 'grid3', 'productName').color, '#445566');
});
