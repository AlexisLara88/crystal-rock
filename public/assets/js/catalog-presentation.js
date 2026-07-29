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
        'baseWidth',
        'baseHeight',
        'widthScale',
        'heightScale',
        'offsetX',
        'offsetY',
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

    const DEFAULT_LAYOUT_RULE = Object.freeze({
        minWidthScale: 0.6,
        maxWidthScale: 1,
        minHeightScale: 0.6,
        maxHeightScale: 1,
        nudgeStep: 4,
        movement: 'both',
    });

    const defineRole = (label, zoneLabel, layout = {}) => ({
        label,
        zoneLabel,
        properties: [...EDITABLE_PROPERTIES],
        layout: {
            ...DEFAULT_LAYOUT_RULE,
            ...layout,
        },
    });

    const ROLE_DEFINITIONS = {
        wordmark: defineRole('Marca', 'Área de marca'),
        coverTitle: defineRole('Título de portada', 'Bloque principal de portada'),
        coverSubtitle: defineRole('Bajada de portada', 'Bloque principal de portada'),
        promotionText: defineRole('Texto promocional', 'Módulo promocional'),
        promotionValue: defineRole('Valor promocional', 'Módulo promocional'),
        promotionBadge: defineRole('Etiqueta promocional', 'Módulo promocional'),
        date: defineRole('Fecha', 'Área segura de portada'),
        category: defineRole('Categoría', 'Cabecera de categoría'),
        featuredLabel: defineRole('Etiqueta destacada', 'Imagen de apertura'),
        productName: defineRole('Nombre de producto', 'Cabecera de producto'),
        productSpecs: defineRole('Especificaciones', 'Ficha técnica'),
        productCode: defineRole('Código', 'Bloque comercial'),
        productPrice: defineRole('Precio', 'Bloque comercial'),
        footerWordmark: defineRole('Marca de pie', 'Pie de página'),
        footerTagline: defineRole('Texto de pie', 'Pie de página'),
        backHeading: defineRole('Título de contraportada', 'Tarjeta de pedidos'),
        backChannel: defineRole('Canal comercial', 'Tarjeta de pedidos'),
        backLink: defineRole('Enlace comercial', 'Tarjeta de pedidos'),
        backNote: defineRole('Nota comercial', 'Tarjeta de pedidos'),
        communityHeading: defineRole('Título de comunidad', 'Llamado a comunidad'),
        communityLink: defineRole('Enlace de comunidad', 'Llamado a comunidad'),
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
        promotionBadge: {
            fontFamily: 'font:sansEditorial',
            fontSize: '14px',
            fontWeight: 800,
            lineHeight: 1,
            color: 'color:wine',
            backgroundColor: 'color:cream',
            textAlign: 'center',
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

    const TEMPLATE_ROLES = {
        coverEditorial: [
            'wordmark',
            'coverTitle',
            'coverSubtitle',
            'promotionText',
            'promotionValue',
            'date',
        ],
        coverPromotional: [
            'wordmark',
            'coverTitle',
            'coverSubtitle',
            'promotionText',
            'promotionValue',
            'promotionBadge',
            'date',
        ],
        coverMinimal: [
            'wordmark',
            'coverTitle',
            'coverSubtitle',
            'date',
        ],
        featured: [
            'category',
            'featuredLabel',
            'productName',
            'productSpecs',
            'productCode',
            'productPrice',
            'footerWordmark',
            'footerTagline',
        ],
        grid4: [
            'productName',
            'productSpecs',
            'productCode',
            'productPrice',
            'footerWordmark',
            'footerTagline',
        ],
        grid3: [
            'productName',
            'productSpecs',
            'productCode',
            'productPrice',
            'footerWordmark',
            'footerTagline',
        ],
        grid2: [
            'productName',
            'productSpecs',
            'productCode',
            'productPrice',
            'footerWordmark',
            'footerTagline',
        ],
        grid1: [
            'productName',
            'productSpecs',
            'productCode',
            'productPrice',
            'footerWordmark',
            'footerTagline',
        ],
        back: [
            'wordmark',
            'backHeading',
            'backChannel',
            'backLink',
            'backNote',
            'communityHeading',
            'communityLink',
        ],
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

        if (!TEMPLATE_ROLES[templateId].includes(role)) {
            throw new Error(`El rol ${role} no pertenece a la plantilla ${templateId}`);
        }
    };

    const createPresentationState = () => ({
        version: 1,
        theme: deepClone(THEME),
        globalRoleOverrides: {},
        templateRoleOverrides: {},
    });

    const validateStylePatch = (state, templateId, role, patch) => {
        if (patch.fontSize !== undefined) {
            const baseSize = Number.parseFloat(resolveDefaultRoleStyle(state, templateId, role).fontSize);
            const requestedSize = Number.parseFloat(patch.fontSize);

            if (
                !Number.isFinite(requestedSize)
                || requestedSize < baseSize * 0.7
                || requestedSize > baseSize * 1.5
            ) {
                throw new Error(`Tamaño fuera del rango permitido para ${role}`);
            }
        }

        if (patch.fontFamily !== undefined) {
            const allowedFonts = Object.keys(state.theme?.fonts ?? THEME.fonts).map((key) => `font:${key}`);

            if (!allowedFonts.includes(patch.fontFamily)) {
                throw new Error(`Tipografía no permitida para ${role}`);
            }
        }

        if (patch.fontWeight !== undefined && ![300, 400, 600, 700, 800].includes(Number(patch.fontWeight))) {
            throw new Error(`Peso no permitido para ${role}`);
        }

        if (patch.textAlign !== undefined && !['left', 'center', 'right'].includes(patch.textAlign)) {
            throw new Error(`Alineación no permitida para ${role}`);
        }

        const layout = ROLE_DEFINITIONS[role].layout;
        const dimensionRanges = {
            widthScale: [layout.minWidthScale, layout.maxWidthScale],
            heightScale: [layout.minHeightScale, layout.maxHeightScale],
        };

        for (const [property, [minimum, maximum]] of Object.entries(dimensionRanges)) {
            if (
                patch[property] !== undefined
                && (
                    !Number.isFinite(Number(patch[property]))
                    || Number(patch[property]) < minimum
                    || Number(patch[property]) > maximum
                )
            ) {
                throw new Error(`Dimensión fuera del rango permitido para ${role}`);
            }
        }

        for (const property of ['baseWidth', 'baseHeight']) {
            if (
                patch[property] !== undefined
                && (!Number.isFinite(Number(patch[property])) || Number(patch[property]) <= 0)
            ) {
                throw new Error(`Dimensión base inválida para ${role}`);
            }
        }

        for (const property of ['offsetX', 'offsetY']) {
            if (patch[property] !== undefined && !Number.isFinite(Number(patch[property]))) {
                throw new Error(`Desplazamiento inválido para ${role}`);
            }
        }
    };

    const resolveDefaultRoleStyle = (state, templateId, role) => {
        assertTemplateAndRole(templateId, role);

        const theme = state.theme ?? THEME;
        const merged = {
            ...BASE_ROLE_STYLES[role],
            ...(TEMPLATE_STYLES[templateId][role] ?? {}),
        };

        return Object.fromEntries(
            Object.entries(merged).map(([property, value]) => [property, resolveToken(value, theme)]),
        );
    };

    const resolveRoleStyle = (state, templateId, role) => {
        assertTemplateAndRole(templateId, role);

        const theme = state.theme ?? THEME;
        const merged = {
            ...resolveDefaultRoleStyle(state, templateId, role),
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

        validateStylePatch(state, templateId, role, patch);

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
        resolveDefaultRoleStyle,
        resolveRoleStyle,
        updateRoleStyle,
        resetRoleStyle,
        getRoleDefinition: (role) => deepClone(ROLE_DEFINITIONS[role] ?? null),
        getTheme: (state) => deepClone(state?.theme ?? THEME),
        listRoles: () => Object.keys(ROLE_DEFINITIONS),
        listTemplates: () => Object.keys(TEMPLATE_STYLES),
        listTemplateRoles: (templateId) => {
            if (!TEMPLATE_ROLES[templateId]) {
                throw new Error(`Plantilla desconocida: ${templateId}`);
            }

            return [...TEMPLATE_ROLES[templateId]];
        },
        isRoleSupported: (templateId, role) => Boolean(TEMPLATE_ROLES[templateId]?.includes(role)),
    });
});
