(function catalogComposerModule(root, factory) {
    const api = factory();

    if (typeof module === 'object' && module.exports) {
        module.exports = api;
    } else {
        root.CatalogComposer = api;
    }
}(typeof globalThis !== 'undefined' ? globalThis : this, () => {
    const SPEC_FIELDS = [
        ['Medidas', 'measurements'],
        ['Material', 'material'],
        ['Embalaje', 'packaging'],
        ['Pack', 'pack'],
        ['Master', 'master'],
    ];

    function splitIntoPages(products, pageSize = 4) {
        const pages = [];

        for (let index = 0; index < products.length; index += pageSize) {
            pages.push(products.slice(index, index + pageSize));
        }

        return pages;
    }

    function normalizeProduct(product) {
        return {
            ...product,
            editorKey: `row-${product.sourceRow}`,
            imageSource: product.image?.preview ?? '',
            specs: SPEC_FIELDS
                .map(([label, field]) => [label, String(product[field] ?? '').trim()])
                .filter(([, value]) => value !== ''),
        };
    }

    function composeCatalog(model, options = {}) {
        if (!model || !Array.isArray(model.products) || model.products.length === 0) {
            throw new Error('No hay productos confirmados para componer.');
        }
        if (!Array.isArray(model.categories) || model.categories.length === 0) {
            throw new Error('No hay categorías confirmadas para componer.');
        }

        const generatedAt = options.generatedAt instanceof Date
            ? options.generatedAt
            : new Date(options.generatedAt ?? Date.now());
        const dateLabel = new Intl.DateTimeFormat('es-AR', {
            day: '2-digit',
            month: '2-digit',
            year: '2-digit',
        }).format(generatedAt);
        const products = model.products.map(normalizeProduct);
        const pages = [{
            id: 'cover-imported',
            type: 'cover',
            label: 'Portada',
            description: `${products.length} productos importados`,
            title: options.coverTitle ?? 'Catálogo',
            subtitle: options.coverSubtitle ?? 'Productos seleccionados para vos',
            updatedLabel: `ACTUALIZADO: ${dateLabel}`,
        }];

        model.categories.forEach((category, categoryIndex) => {
            const categoryProducts = products.filter(product => product.category === category.name);
            if (categoryProducts.length === 0) return;

            const [featuredProduct, ...remainingProducts] = categoryProducts;
            const categoryId = `category-${categoryIndex + 1}`;

            pages.push({
                id: `${categoryId}-featured`,
                type: 'featured',
                label: `Destacado · ${category.name}`,
                description: 'Apertura de categoría',
                category: category.name,
                product: featuredProduct,
            });

            splitIntoPages(remainingProducts).forEach((pageProducts, pageIndex) => {
                pages.push({
                    id: `${categoryId}-grid-${pageIndex + 1}`,
                    type: 'grid',
                    count: pageProducts.length,
                    label: `${category.name} · ${pageProducts.length} producto${pageProducts.length === 1 ? '' : 's'}`,
                    description: `Grilla ${pageIndex + 1} de la categoría`,
                    category: category.name,
                    products: pageProducts,
                });
            });
        });

        pages.push({
            id: 'back-imported',
            type: 'back',
            label: 'Contraportada',
            description: 'Canales comerciales y comunidad',
        });

        return pages;
    }

    return {
        composeCatalog,
        normalizeProduct,
        splitIntoPages,
    };
}));
