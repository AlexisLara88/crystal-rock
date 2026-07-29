((root, factory) => {
    const api = factory();

    if (typeof module === 'object' && module.exports) {
        module.exports = api;
    }

    if (root) {
        root.CatalogPresentation = api;
    }
})(typeof globalThis !== 'undefined' ? globalThis : this, () => {
    'use strict';

    const EDITABLE_PROPERTIES = [
        'fontFamily',
        'fontSize',
        'fontWeight',
        'lineHeight',
        'color',
        'backgroundColor',
        'textAlign',
    ];

    const THEME = {
        fonts: {
            sansEditorial: '"Avenir Next", "Century Gothic", "Trebuchet MS", sans-serif',
            sansCompact: '"Arial Narrow", "Liberation Sans Narrow", Arial, sans-serif',
            serifAccent: 'Georgia, "Times New Roman", serif',
        },
        colors: {
            wine: '#702d45',
            wineDark: '#4e1f31',
            cream: '#f5ecde',
            ink: '#29332f',
            muted: '#6c746e',
            sage: '#8c9b85',
            sageDark: '#586a5b',
            terracotta: '#c9795b',
            paper: '#fcf9f3',
            white: '#ffffff',
            transparent: 'transparent',
        },
    };

    const ROLE_DEFINITIONS = {
        wordmark: { label: 'Marca', properties: EDITABLE_PROPERTIES },
        coverTitle: { label: 'Título de portada', properties: EDITABLE_PROPERTIES },
        coverSubtitle: { label: 'Bajada de portada', properties: EDITABLE_PROPERTIES },
        promotionText: { label: 'Texto promocional', properties: EDITABLE_PROPERTIES },
        promotionValue: { label: 'Valor promocional', properties: EDITABLE_PROPERTIES },
        date: { label: 'Fecha', properties: EDITABLE_PROPERTIES },
        category: { label: 'Categoría', properties: EDITABLE_PROPERTIES },
        featuredLabel: { label: 'Etiqueta destacada', properties: EDITABLE_PROPERTIES },
        productName: { label: 'Nombre de producto', properties: EDITABLE_PROPERTIES },
        productSpecs: { label: 'Especificaciones', properties: EDITABLE_PROPERTIES },
        productCode: { label: 'Código', properties: EDITABLE_PROPERTIES },
        productPrice: { label: 'Precio', properties: EDITABLE_PROPERTIES },
        footerWordmark: { label: 'Marca de pie', properties: EDITABLE_PROPERTIES },
        footerTagline: { label: 'Texto de pie', properties: EDITABLE_PROPERTIES },
        backHeading: { label: 'Título de contraportada', properties: EDITABLE_PROPERTIES },
        backChannel: { label: 'Canal comercial', properties: EDITABLE_PROPERTIES },
        backLink: { label: 'Enlace comercial', properties: EDITABLE_PROPERTIES },
        backNote: { label: 'Nota comercial', properties: EDITABLE_PROPERTIES },
        communityHeading: { label: 'Título de comunidad', properties: EDITABLE_PROPERTIES },
        communityLink: { label: 'Enlace de comunidad', properties: EDITABLE_PROPERTIES },
    };

    const BASE_ROLE_STYLES = {
        wordmark: {
            fontFamily: 'font:serifAccent',
            fontSize: '22px',
            fontWeight: 400,
            lineHeight: 1.1,
            color: 'color:cream',
            backgroundColor: 'color:wine',
            textAlign: 'center',
        },
        coverTitle: {
            fontFamily: 'font:sansEditorial',
            fontSize: '60px',
            fontWeight: 400,
            lineHeight: 1,
            color: 'color:cream',
            backgroundColor: 'color:transparent',
            textAlign: 'center',
        },
        coverSubtitle: {
            fontFamily: 'font:sansEditorial',
            fontSize: '16px',
            fontWeight: 400,
            lineHeight: 1.2,
            color: 'color:cream',
            backgroundColor: 'color:transparent',
            textAlign: 'center',
        },
        promotionText: {
            fontFamily: 'font:sansEditorial',
            fontSize: '15px',
            fontWeight: 400,
            lineHeight: 1.05,
            color: 'color:cream',
            backgroundColor: 'color:transparent',
            textAlign: 'left',
        },
        promotionValue: {
            fontFamily: 'font:sansEditorial',
            fontSize: '34px',
            fontWeight: 800,
            lineHeight: 1,
            color: 'color:cream',
            backgroundColor: 'color:transparent',
            textAlign: 'left',
        },
        date: {
            fontFamily: 'font:sansEditorial',
            fontSize: '12px',
            fontWeight: 400,
            lineHeight: 1,
            color: 'color:cream',
            backgroundColor: 'color:wine',
            textAlign: 'center',
        },
        category: {
            fontFamily: 'font:sansEditorial',
            fontSize: '27px',
            fontWeight: 300,
            lineHeight: 1,
            color: 'color:cream',
            backgroundColor: 'color:sageDark',
            textAlign: 'left',
        },
        featuredLabel: {
            fontFamily: 'font:sansEditorial',
            fontSize: '20px',
            fontWeight: 400,
            lineHeight: 0.95,
            color: 'color:wineDark',
            backgroundColor: 'rgba(245, 236, 222, .9)',
            textAlign: 'left',
        },
        productName: {
            fontFamily: 'font:sansEditorial',
            fontSize: '20px',
            fontWeight: 700,
            lineHeight: 1.05,
            color: 'color:wine',
            backgroundColor: 'rgba(140, 155, 133, .1)',
            textAlign: 'left',
        },
        productSpecs: {
            fontFamily: 'font:sansEditorial',
            fontSize: '13px',
            fontWeight: 400,
            lineHeight: 1.08,
            color: '#716c67',
            backgroundColor: 'color:transparent',
            textAlign: 'left',
        },
        productCode: {
            fontFamily: 'font:sansEditorial',
            fontSize: '12px',
            fontWeight: 400,
            lineHeight: 1,
            color: 'color:wine',
            backgroundColor: 'color:transparent',
            textAlign: 'center',
        },
        productPrice: {
            fontFamily: 'font:sansEditorial',
            fontSize: '13px',
            fontWeight: 800,
            lineHeight: 1,
            color: 'color:white',
            backgroundColor: 'color:terracotta',
            textAlign: 'center',
        },
        footerWordmark: {
            fontFamily: 'font:serifAccent',
            fontSize: '14px',
            fontWeight: 400,
            lineHeight: 1,
            color: 'color:wine',
            backgroundColor: 'color:transparent',
            textAlign: 'left',
        },
        footerTagline: {
            fontFamily: 'font:sansEditorial',
            fontSize: '10px',
            fontWeight: 400,
            lineHeight: 1,
            color: 'color:wine',
            backgroundColor: 'color:transparent',
            textAlign: 'right',
        },
        backHeading: {
            fontFamily: 'font:sansEditorial',
            fontSize: '22px',
            fontWeight: 700,
            lineHeight: 1.1,
            color: 'color:cream',
            backgroundColor: 'color:transparent',
            textAlign: 'center',
        },
        backChannel: {
            fontFamily: 'font:sansEditorial',
            fontSize: '16px',
            fontWeight: 700,
            lineHeight: 1.1,
            color: 'color:cream',
            backgroundColor: 'color:transparent',
            textAlign: 'center',
        },
        backLink: {
            fontFamily: 'font:sansEditorial',
            fontSize: '16px',
            fontWeight: 400,
            lineHeight: 1,
            color: 'color:cream',
            backgroundColor: 'color:transparent',
            textAlign: 'left',
        },
        backNote: {
            fontFamily: 'font:sansEditorial',
            fontSize: '16px',
            fontWeight: 400,
            lineHeight: 1.1,
            color: 'color:cream',
            backgroundColor: 'color:transparent',
            textAlign: 'center',
        },
        communityHeading: {
            fontFamily: 'font:sansEditorial',
            fontSize: '20px',
            fontWeight: 700,
            lineHeight: 1.05,
            color: 'color:cream',
            backgroundColor: 'color:transparent',
            textAlign: 'center',
        },
        communityLink: {
            fontFamily: 'font:sansEditorial',
            fontSize: '19px',
            fontWeight: 400,
            lineHeight: 1,
            color: 'color:white',
            backgroundColor: 'color:wine',
            textAlign: 'center',
        },
    };

    const TEMPLATE_STYLES = {
        coverEditorial: {},
        coverPromotional: {
            wordmark: { fontSize: '18px', backgroundColor: 'color:transparent' },
            coverTitle: { fontSize: '52px', textAlign: 'left' },
            coverSubtitle: { textAlign: 'left' },
            promotionValue: { fontSize: '32px' },
            date: { backgroundColor: 'color:terracotta' },
        },
        coverMinimal: {
            wordmark: { backgroundColor: 'color:sageDark' },
            coverTitle: { fontSize: '62px', color: 'color:wine', textAlign: 'left' },
            coverSubtitle: { color: 'color:ink', textAlign: 'left' },
            date: { backgroundColor: 'color:sageDark' },
        },
        featured: {
            productName: { fontSize: '28px' },
            productSpecs: { fontSize: '15px' },
            productCode: { fontSize: '17px' },
            productPrice: { fontSize: '18px' },
        },
        grid4: {},
        grid3: {
            productName: { fontSize: '23px' },
            productSpecs: { fontSize: '14px' },
            productPrice: { fontSize: '16px' },
        },
        grid2: {
            productName: { fontSize: '29px' },
            productSpecs: { fontSize: '16px', lineHeight: 1.22 },
            productCode: { fontSize: '16px' },
            productPrice: { fontSize: '18px' },
        },
        grid1: {
            productName: { fontSize: '42px' },
            productSpecs: { fontSize: '20px', lineHeight: 1.4 },
            productCode: { fontSize: '19px' },
            productPrice: { fontSize: '21px' },
        },
        back: {
            wordmark: {
                fontSize: '34px',
                color: 'color:cream',
                backgroundColor: 'color:transparent',
            },
        },
    };

    const deepClone = (value) => JSON.parse(JSON.stringify(value));

    const resolveToken = (value, theme) => {
        if (typeof value !== 'string' || !value.includes(':')) return value;

        const [type, token] = value.split(':', 2);

        if (type === 'font' && theme.fonts[token]) return theme.fonts[token];
        if (type === 'color' && theme.colors[token]) return theme.colors[token];

        return value;
    };

    const assertTemplateAndRole = (templateId, role) => {
        if (!TEMPLATE_STYLES[templateId]) {
            throw new Error(`Plantilla desconocida: ${templateId}`);
        }

        if (!ROLE_DEFINITIONS[role]) {
            throw new Error(`Rol visual desconocido: ${role}`);
        }
    };

    const createPresentationState = () => ({
        version: 1,
        theme: deepClone(THEME),
        globalRoleOverrides: {},
        templateRoleOverrides: {},
    });

    const resolveRoleStyle = (state, templateId, role) => {
        assertTemplateAndRole(templateId, role);

        const theme = state.theme ?? THEME;
        const merged = {
            ...BASE_ROLE_STYLES[role],
            ...(TEMPLATE_STYLES[templateId][role] ?? {}),
            ...(state.globalRoleOverrides?.[role] ?? {}),
            ...(state.templateRoleOverrides?.[templateId]?.[role] ?? {}),
        };

        return Object.fromEntries(
            Object.entries(merged).map(([property, value]) => [property, resolveToken(value, theme)]),
        );
    };

    const updateRoleStyle = (state, templateId, role, patch) => {
        assertTemplateAndRole(templateId, role);

        const allowed = ROLE_DEFINITIONS[role].properties;
        const invalidProperty = Object.keys(patch).find((property) => !allowed.includes(property));

        if (invalidProperty) {
            throw new Error(`Propiedad no permitida para ${role}: ${invalidProperty}`);
        }

        state.templateRoleOverrides[templateId] ??= {};
        state.templateRoleOverrides[templateId][role] = {
            ...(state.templateRoleOverrides[templateId][role] ?? {}),
            ...deepClone(patch),
        };

        return resolveRoleStyle(state, templateId, role);
    };

    const resetRoleStyle = (state, templateId, role) => {
        assertTemplateAndRole(templateId, role);

        if (state.templateRoleOverrides[templateId]) {
            delete state.templateRoleOverrides[templateId][role];
        }

        return resolveRoleStyle(state, templateId, role);
    };

    return Object.freeze({
        createPresentationState,
        resolveRoleStyle,
        updateRoleStyle,
        resetRoleStyle,
        getRoleDefinition: (role) => deepClone(ROLE_DEFINITIONS[role] ?? null),
        listRoles: () => Object.keys(ROLE_DEFINITIONS),
        listTemplates: () => Object.keys(TEMPLATE_STYLES),
    });
});
