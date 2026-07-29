(() => {
    const { createApp } = Vue;

    createApp({
        data() {
            return {
                currentPage: 0,
                showAll: false,
                coverVariant: 'editorial',
                activeImageKey: null,
                imageAdjustments: {},
                dragState: null,
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
            activeImageAdjustment() {
                if (!this.activeImageKey) return null;

                return this.imageAdjustments[this.activeImageKey] ?? null;
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
            ensureImageAdjustment(key, label = 'Imagen de producto') {
                if (!this.imageAdjustments[key]) {
                    this.imageAdjustments[key] = {
                        label,
                        zoom: 1,
                        x: 0,
                        y: 0,
                    };
                }

                return this.imageAdjustments[key];
            },
            imageStyle(key) {
                const adjustment = this.imageAdjustments[key] ?? { zoom: 1, x: 0, y: 0 };

                return {
                    transform: `translate3d(${adjustment.x}%, ${adjustment.y}%, 0) scale(${adjustment.zoom})`,
                };
            },
            startImageDrag(key, label, event) {
                event.preventDefault();

                const adjustment = this.ensureImageAdjustment(key, label);
                const bounds = event.currentTarget.getBoundingClientRect();

                this.activeImageKey = key;
                this.dragState = {
                    key,
                    pointerId: event.pointerId,
                    startX: event.clientX,
                    startY: event.clientY,
                    originX: adjustment.x,
                    originY: adjustment.y,
                    width: bounds.width,
                    height: bounds.height,
                };

                event.currentTarget.setPointerCapture?.(event.pointerId);
            },
            dragImage(event) {
                if (!this.dragState || this.dragState.pointerId !== event.pointerId) return;

                const adjustment = this.imageAdjustments[this.dragState.key];
                const deltaX = ((event.clientX - this.dragState.startX) / this.dragState.width) * 100;
                const deltaY = ((event.clientY - this.dragState.startY) / this.dragState.height) * 100;

                adjustment.x = this.clamp(this.dragState.originX + deltaX, -50, 50);
                adjustment.y = this.clamp(this.dragState.originY + deltaY, -50, 50);
            },
            endImageDrag(event) {
                if (!this.dragState || this.dragState.pointerId !== event.pointerId) return;

                event.currentTarget.releasePointerCapture?.(event.pointerId);
                this.dragState = null;
            },
            setImageZoom(event) {
                if (!this.activeImageAdjustment) return;

                this.activeImageAdjustment.zoom = this.clamp(Number(event.target.value), 0.7, 2.4);
            },
            nudgeImage(deltaX, deltaY) {
                if (!this.activeImageAdjustment) return;

                this.activeImageAdjustment.x = this.clamp(this.activeImageAdjustment.x + deltaX, -50, 50);
                this.activeImageAdjustment.y = this.clamp(this.activeImageAdjustment.y + deltaY, -50, 50);
            },
            resetImageAdjustment() {
                if (!this.activeImageAdjustment) return;

                this.activeImageAdjustment.zoom = 1;
                this.activeImageAdjustment.x = 0;
                this.activeImageAdjustment.y = 0;
            },
            clamp(value, minimum, maximum) {
                return Math.min(Math.max(value, minimum), maximum);
            },
        },
    }).mount('#catalog-app');
})();
