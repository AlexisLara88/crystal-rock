(() => {
    const { createApp } = Vue;

    createApp({
        data() {
            return {
                currentPage: 0,
                showAll: false,
                coverVariant: 'editorial',
                pages: [
                    { id: 'cover', type: 'cover', label: 'Portada', description: 'Tres tratamientos disponibles' },
                    { id: 'featured', type: 'featured', label: 'Producto destacado', description: 'Apertura de categoría' },
                    { id: 'grid-4', type: 'grid', count: 4, label: 'Grilla · 4 productos', description: 'Página de máximo rendimiento' },
                    { id: 'grid-3', type: 'grid', count: 3, label: 'Grilla · 3 productos', description: 'Cierre asimétrico equilibrado' },
                    { id: 'grid-2', type: 'grid', count: 2, label: 'Grilla · 2 productos', description: 'Dos fichas amplias' },
                    { id: 'grid-1', type: 'grid', count: 1, label: 'Grilla · 1 producto', description: 'Ficha protagonista' },
                    { id: 'back', type: 'back', label: 'Contraportada', description: 'Canales comerciales y comunidad' },
                ],
                featuredProduct: {
                    code: '6676159',
                    price: '$2,526.72',
                    specs: [
                        ['Medidas', '8.1 × 21.8 cm'],
                        ['Material', 'Vidrio'],
                        ['Embalaje', 'Caja color'],
                        ['Pack', '6u'],
                        ['Master', '24u'],
                    ],
                },
                products: [
                    {
                        name: 'Copas Degustación 465 ML',
                        code: '6677029',
                        price: '$1,642.32',
                        image: 'glass-tasting.png',
                        specs: [['Medidas', '7 × 22.5 cm'], ['Material', 'Vidrio'], ['Embalaje', 'Caja color'], ['Pack', '6u'], ['Master', '48u']],
                    },
                    {
                        name: 'Copas Degustación 435 ML',
                        code: '6675202',
                        price: '$1,925.47',
                        image: 'glass-premium.png',
                        specs: [['Medidas', '7.5 × 22.3 cm'], ['Material', 'Vidrio'], ['Embalaje', 'Caja color'], ['Pack', '6u'], ['Master', '24u']],
                    },
                    {
                        name: 'Copas Premium 615 ML',
                        code: '6675749',
                        price: '$3,726.91',
                        image: 'glass-wine.png',
                        specs: [['Medidas', '7 × 24.5 cm'], ['Material', 'Vidrio'], ['Embalaje', 'Caja color'], ['Pack', '6u'], ['Master', '24u']],
                    },
                    {
                        name: 'Sacacorcho a pilas',
                        code: '57913854',
                        price: '$3,158.40',
                        image: 'corkscrew-electric.png',
                        specs: [['Medidas', '26 cm'], ['Material', 'Acrílico'], ['Embalaje', 'Caja color'], ['Pack', 'Unidad'], ['Master', '30u']],
                    },
                ],
            };
        },
        computed: {
            visiblePages() {
                return this.showAll ? this.pages : [this.pages[this.currentPage]];
            },
        },
        methods: {
            asset(file) {
                return `${window.CATALOG_ASSET_BASE}${file}`;
            },
            selectPage(index) {
                this.currentPage = index;
                this.showAll = false;
            },
            previousPage() {
                if (this.currentPage > 0) this.currentPage -= 1;
            },
            nextPage() {
                if (this.currentPage < this.pages.length - 1) this.currentPage += 1;
            },
        },
    }).mount('#catalog-app');
})();
