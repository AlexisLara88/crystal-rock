(() => {
    const { createApp } = Vue;
    const presentationEngine = window.CatalogPresentation;

    createApp({
        data() {
            return {
                presentationState: presentationEngine.createPresentationState(),
                currentPage: 0,
                showAll: false,
                coverVariant: 'editorial',
                activeImageKey: null,
                activeTextSelection: null,
                selectedTextElement: null,
                textDragState: null,
                layoutWarning: '',
                imageAdjustments: {},
                dragState: null,
                panelDragState: null,
                floatingEditorPosition: {
                    top: 96,
                    left: 12,
                },
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
            activeTextInspector() {
                if (!this.activeTextSelection) return null;

                const definition = presentationEngine.getRoleDefinition(this.activeTextSelection.role);

                return {
                    ...this.activeTextSelection,
                    label: definition.label,
                    zoneLabel: definition.zoneLabel,
                };
            },
            activeTextControlState() {
                if (!this.activeTextSelection) return null;

                const { templateId, role } = this.activeTextSelection;
                const current = presentationEngine.resolveRoleStyle(this.presentationState, templateId, role);
                const defaults = presentationEngine.resolveDefaultRoleStyle(this.presentationState, templateId, role);
                const theme = presentationEngine.getTheme(this.presentationState);
                const baseSize = Number.parseFloat(defaults.fontSize);
                const currentSize = Number.parseFloat(current.fontSize);
                const fontKey = Object.entries(theme.fonts)
                    .find(([, value]) => value === current.fontFamily)?.[0] ?? 'sansEditorial';
                const layout = presentationEngine.getRoleDefinition(role).layout;

                return {
                    current,
                    defaults,
                    fontKey,
                    fontSize: currentSize,
                    minFontSize: Math.max(6, Math.ceil(baseSize * 0.7)),
                    maxFontSize: Math.floor(baseSize * 1.5),
                    widthPercent: Math.round((current.widthScale ?? 1) * 100),
                    heightPercent: Math.round((current.heightScale ?? 1) * 100),
                    offsetX: Math.round(current.offsetX ?? 0),
                    offsetY: Math.round(current.offsetY ?? 0),
                    minWidthPercent: Math.round(layout.minWidthScale * 100),
                    maxWidthPercent: Math.round(layout.maxWidthScale * 100),
                    minHeightPercent: Math.round(layout.minHeightScale * 100),
                    maxHeightPercent: Math.round(layout.maxHeightScale * 100),
                    nudgeStep: layout.nudgeStep,
                };
            },
            fontOptions() {
                return [
                    { value: 'sansEditorial', label: 'Sans editorial' },
                    { value: 'sansCompact', label: 'Sans compacta' },
                    { value: 'serifAccent', label: 'Serif de acento' },
                ];
            },
            textColorPalette() {
                const colors = presentationEngine.getTheme(this.presentationState).colors;

                return [
                    { name: 'Vino', value: colors.wine },
                    { name: 'Vino oscuro', value: colors.wineDark },
                    { name: 'Terracota', value: colors.terracotta },
                    { name: 'Salvia', value: colors.sage },
                    { name: 'Salvia oscura', value: colors.sageDark },
                    { name: 'Arena', value: colors.cream },
                    { name: 'Papel', value: colors.paper },
                    { name: 'Tinta', value: colors.ink },
                    { name: 'Blanco', value: colors.white },
                ];
            },
            floatingEditorStyle() {
                return {
                    top: `${this.floatingEditorPosition.top}px`,
                    left: `${this.floatingEditorPosition.left}px`,
                    maxHeight: `calc(100vh - ${this.floatingEditorPosition.top + 12}px)`,
                };
            },
        },
        mounted() {
            document.addEventListener('pointerdown', this.handleDocumentPointerDown);
            document.addEventListener('pointermove', this.dragText);
            document.addEventListener('pointermove', this.dragFloatingEditor);
            document.addEventListener('pointerup', this.endTextDrag);
            document.addEventListener('pointerup', this.endFloatingEditorDrag);
            document.addEventListener('pointercancel', this.endTextDrag);
            document.addEventListener('pointercancel', this.endFloatingEditorDrag);
            window.addEventListener('resize', this.constrainFloatingEditor);
            this.dockFloatingEditor();
        },
        beforeUnmount() {
            document.removeEventListener('pointerdown', this.handleDocumentPointerDown);
            document.removeEventListener('pointermove', this.dragText);
            document.removeEventListener('pointermove', this.dragFloatingEditor);
            document.removeEventListener('pointerup', this.endTextDrag);
            document.removeEventListener('pointerup', this.endFloatingEditorDrag);
            document.removeEventListener('pointercancel', this.endTextDrag);
            document.removeEventListener('pointercancel', this.endFloatingEditorDrag);
            window.removeEventListener('resize', this.constrainFloatingEditor);
        },
        watch: {
            coverVariant() {
                this.deselectAll();
            },
        },
        methods: {
            asset(file) {
                return `${window.CATALOG_ASSET_BASE}${file}`;
            },
            textStyle(templateId, role) {
                const resolved = presentationEngine.resolveRoleStyle(this.presentationState, templateId, role);
                const {
                    baseWidth,
                    baseHeight,
                    widthScale = 1,
                    heightScale = 1,
                    offsetX = 0,
                    offsetY = 0,
                    ...style
                } = resolved;

                if (baseWidth) style.width = `${baseWidth * widthScale}px`;
                if (baseHeight) style.height = `${baseHeight * heightScale}px`;

                style.transform = `translate3d(${offsetX}px, ${offsetY}px, 0)`;
                style.transformOrigin = 'center';

                if (['productName', 'productCode', 'productPrice', 'communityLink'].includes(role)) {
                    style.justifyContent = {
                        left: 'flex-start',
                        center: 'center',
                        right: 'flex-end',
                    }[style.textAlign];
                }

                return style;
            },
            coverTemplateId() {
                const variants = {
                    editorial: 'coverEditorial',
                    promotional: 'coverPromotional',
                    minimal: 'coverMinimal',
                };

                return variants[this.coverVariant];
            },
            gridTemplateId(count) {
                return `grid${count}`;
            },
            templateLabel(templateId) {
                const labels = {
                    coverEditorial: 'Portada · Editorial',
                    coverPromotional: 'Portada · Campaña',
                    coverMinimal: 'Portada · Minimal claro',
                    featured: 'Producto destacado',
                    grid4: 'Grilla · 4 productos',
                    grid3: 'Grilla · 3 productos',
                    grid2: 'Grilla · 2 productos',
                    grid1: 'Grilla · 1 producto',
                    back: 'Contraportada',
                };

                return labels[templateId] ?? templateId;
            },
            isTextSelected(templateId, role) {
                return this.activeTextSelection?.templateId === templateId
                    && this.activeTextSelection?.role === role;
            },
            textSelectionClass(templateId, role) {
                return {
                    'editable-text': true,
                    'active-text': this.isTextSelected(templateId, role),
                };
            },
            selectText(templateId, role, event) {
                if (event.button !== 0) return;

                this.activeImageKey = null;
                this.dragState = null;
                this.layoutWarning = '';
                this.activeTextSelection = {
                    templateId,
                    templateLabel: this.templateLabel(templateId),
                    role,
                };
                this.selectedTextElement = event.currentTarget;
                this.ensureSelectedTextDimensions(event.currentTarget);
                this.prepareFloatingEditor();
                this.startTextDrag(event);
            },
            selectPage(index) {
                this.deselectAll();
                this.currentPage = index;
                this.showAll = false;
            },
            previousPage() {
                if (this.currentPage > 0) {
                    this.deselectAll();
                    this.currentPage -= 1;
                }
            },
            nextPage() {
                if (this.currentPage < this.pages.length - 1) {
                    this.deselectAll();
                    this.currentPage += 1;
                }
            },
            toggleShowAll() {
                this.deselectAll();
                this.showAll = !this.showAll;
            },
            ensureImageAdjustment(key, label = 'Imagen de producto') {
                if (!this.imageAdjustments[key]) {
                    this.imageAdjustments[key] = {
                        label,
                        zoom: 1,
                        x: 0,
                        y: 0,
                        maskSize: 0.9,
                        rotation: 0,
                    };
                }

                return this.imageAdjustments[key];
            },
            imageStyle(key) {
                const adjustment = this.imageAdjustments[key] ?? { zoom: 1, x: 0, y: 0, rotation: 0 };

                return {
                    transform: `translate3d(${adjustment.x}%, ${adjustment.y}%, 0) scale(${adjustment.zoom}) rotate(${adjustment.rotation}deg)`,
                };
            },
            maskStyle(key) {
                const adjustment = this.imageAdjustments[key] ?? { maskSize: 0.9 };
                const size = `${adjustment.maskSize * 100}%`;

                return {
                    width: size,
                    height: size,
                };
            },
            startImageDrag(key, label, event) {
                event.preventDefault();

                const adjustment = this.ensureImageAdjustment(key, label);
                const bounds = event.currentTarget.getBoundingClientRect();

                this.activeTextSelection = null;
                this.activeImageKey = key;
                this.prepareFloatingEditor();
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
            setMaskSize(event) {
                if (!this.activeImageAdjustment) return;

                this.activeImageAdjustment.maskSize = this.clamp(Number(event.target.value), 0.6, 1);
            },
            nudgeImage(deltaX, deltaY) {
                if (!this.activeImageAdjustment) return;

                this.activeImageAdjustment.x = this.clamp(this.activeImageAdjustment.x + deltaX, -50, 50);
                this.activeImageAdjustment.y = this.clamp(this.activeImageAdjustment.y + deltaY, -50, 50);
            },
            rotateImage(degrees) {
                if (!this.activeImageAdjustment) return;

                let rotation = this.activeImageAdjustment.rotation + degrees;

                if (rotation > 180) rotation -= 360;
                if (rotation < -180) rotation += 360;

                this.activeImageAdjustment.rotation = rotation;
            },
            resetImageAdjustment() {
                if (!this.activeImageAdjustment) return;

                this.activeImageAdjustment.zoom = 1;
                this.activeImageAdjustment.x = 0;
                this.activeImageAdjustment.y = 0;
                this.activeImageAdjustment.maskSize = 0.9;
                this.activeImageAdjustment.rotation = 0;
            },
            dockFloatingEditor() {
                const viewportPadding = 12;
                const panelWidth = 300;

                this.floatingEditorPosition = {
                    top: 96,
                    left: Math.max(viewportPadding, window.innerWidth - panelWidth - 24),
                };
            },
            prepareFloatingEditor() {
                this.$nextTick(() => this.constrainFloatingEditor());
            },
            startFloatingEditorDrag(event) {
                if (event.button !== 0) return;

                event.preventDefault();
                const panel = event.currentTarget.closest('.floating-element-editor');

                if (!panel) return;

                this.panelDragState = {
                    pointerId: event.pointerId,
                    startX: event.clientX,
                    startY: event.clientY,
                    originLeft: this.floatingEditorPosition.left,
                    originTop: this.floatingEditorPosition.top,
                    panelWidth: panel.offsetWidth,
                };

                event.currentTarget.setPointerCapture?.(event.pointerId);
            },
            dragFloatingEditor(event) {
                if (!this.panelDragState || this.panelDragState.pointerId !== event.pointerId) return;

                event.preventDefault();
                const viewportPadding = 12;
                const minimumVisibleHeight = 180;
                const maxLeft = Math.max(
                    viewportPadding,
                    window.innerWidth - this.panelDragState.panelWidth - viewportPadding,
                );
                const maxTop = Math.max(viewportPadding, window.innerHeight - minimumVisibleHeight);

                this.floatingEditorPosition = {
                    left: this.clamp(
                        this.panelDragState.originLeft + event.clientX - this.panelDragState.startX,
                        viewportPadding,
                        maxLeft,
                    ),
                    top: this.clamp(
                        this.panelDragState.originTop + event.clientY - this.panelDragState.startY,
                        viewportPadding,
                        maxTop,
                    ),
                };
            },
            endFloatingEditorDrag(event) {
                if (!this.panelDragState || this.panelDragState.pointerId !== event.pointerId) return;

                this.panelDragState = null;
            },
            constrainFloatingEditor() {
                const panel = document.querySelector('.floating-element-editor');
                const panelWidth = panel?.offsetWidth ?? 300;
                const viewportPadding = 12;
                const minimumVisibleHeight = 180;

                this.floatingEditorPosition = {
                    left: this.clamp(
                        this.floatingEditorPosition.left,
                        viewportPadding,
                        Math.max(viewportPadding, window.innerWidth - panelWidth - viewportPadding),
                    ),
                    top: this.clamp(
                        this.floatingEditorPosition.top,
                        viewportPadding,
                        Math.max(viewportPadding, window.innerHeight - minimumVisibleHeight),
                    ),
                };
            },
            handleDocumentPointerDown(event) {
                if (!this.activeImageKey && !this.activeTextSelection) return;
                if (
                    event.target instanceof Element
                    && event.target.closest('.image-mask, .editable-text, .floating-element-editor')
                ) return;

                this.deselectAll();
            },
            deselectImage() {
                this.activeImageKey = null;
                this.dragState = null;
            },
            deselectText() {
                this.activeTextSelection = null;
                this.selectedTextElement = null;
                this.textDragState = null;
                this.layoutWarning = '';
            },
            deselectAll() {
                this.deselectImage();
                this.deselectText();
            },
            resetSelectedText() {
                if (!this.activeTextSelection) return;

                presentationEngine.resetRoleStyle(
                    this.presentationState,
                    this.activeTextSelection.templateId,
                    this.activeTextSelection.role,
                );
                this.layoutWarning = '';
                this.$nextTick(() => {
                    if (this.selectedTextElement) this.ensureSelectedTextDimensions(this.selectedTextElement);
                });
            },
            updateSelectedText(patch) {
                if (!this.activeTextSelection) return;

                presentationEngine.updateRoleStyle(
                    this.presentationState,
                    this.activeTextSelection.templateId,
                    this.activeTextSelection.role,
                    patch,
                );
            },
            ensureSelectedTextDimensions(element) {
                if (!this.activeTextSelection || !element) return;

                const current = presentationEngine.resolveRoleStyle(
                    this.presentationState,
                    this.activeTextSelection.templateId,
                    this.activeTextSelection.role,
                );

                if (current.baseWidth && current.baseHeight) return;

                this.updateSelectedText({
                    baseWidth: Math.max(1, element.offsetWidth),
                    baseHeight: Math.max(1, element.offsetHeight),
                    widthScale: 1,
                    heightScale: 1,
                    offsetX: 0,
                    offsetY: 0,
                });
            },
            startTextDrag(event) {
                if (!this.activeTextSelection || !this.selectedTextElement) return;

                const current = presentationEngine.resolveRoleStyle(
                    this.presentationState,
                    this.activeTextSelection.templateId,
                    this.activeTextSelection.role,
                );
                const bounds = this.selectedTextElement.getBoundingClientRect();

                this.textDragState = {
                    pointerId: event.pointerId,
                    startX: event.clientX,
                    startY: event.clientY,
                    originX: current.offsetX ?? 0,
                    originY: current.offsetY ?? 0,
                    scaleX: bounds.width / Math.max(1, this.selectedTextElement.offsetWidth),
                    scaleY: bounds.height / Math.max(1, this.selectedTextElement.offsetHeight),
                    previousX: current.offsetX ?? 0,
                    previousY: current.offsetY ?? 0,
                };
            },
            dragText(event) {
                if (!this.textDragState || this.textDragState.pointerId !== event.pointerId) return;

                event.preventDefault();
                const candidateX = this.textDragState.originX
                    + ((event.clientX - this.textDragState.startX) / this.textDragState.scaleX);
                const candidateY = this.textDragState.originY
                    + ((event.clientY - this.textDragState.startY) / this.textDragState.scaleY);

                this.applySelectedLayoutPatch({
                    offsetX: Math.round(candidateX),
                    offsetY: Math.round(candidateY),
                }, {
                    offsetX: this.textDragState.previousX,
                    offsetY: this.textDragState.previousY,
                }, true);
            },
            endTextDrag(event) {
                if (!this.textDragState || this.textDragState.pointerId !== event.pointerId) return;

                this.textDragState = null;
            },
            nudgeSelectedText(deltaX, deltaY) {
                if (!this.activeTextControlState) return;

                const previous = {
                    offsetX: this.activeTextControlState.offsetX,
                    offsetY: this.activeTextControlState.offsetY,
                };

                this.applySelectedLayoutPatch({
                    offsetX: previous.offsetX + (deltaX * this.activeTextControlState.nudgeStep),
                    offsetY: previous.offsetY + (deltaY * this.activeTextControlState.nudgeStep),
                }, previous);
            },
            setSelectedDimension(property, event) {
                if (!this.activeTextControlState) return;

                const previous = {
                    widthScale: this.activeTextControlState.current.widthScale ?? 1,
                    heightScale: this.activeTextControlState.current.heightScale ?? 1,
                };

                this.applySelectedLayoutPatch({
                    [property]: Number(event.target.value) / 100,
                }, previous);
            },
            applySelectedLayoutPatch(patch, previous, dragging = false) {
                if (!this.activeTextSelection) return;

                this.updateSelectedText(patch);
                this.$nextTick(() => {
                    if (this.selectedLayoutIsValid()) {
                        this.layoutWarning = '';

                        if (dragging && this.textDragState) {
                            this.textDragState.previousX = patch.offsetX;
                            this.textDragState.previousY = patch.offsetY;
                        }

                        return;
                    }

                    this.updateSelectedText(previous);
                    this.layoutWarning = 'Movimiento bloqueado: el elemento alcanzó el límite de su zona o tocaría otro bloque.';
                });
            },
            selectedLayoutIsValid() {
                const selected = [...document.querySelectorAll('.editable-text.active-text')];

                return selected.every((element) => {
                    const parent = element.parentElement;

                    if (!parent) return false;

                    const bounds = element.getBoundingClientRect();
                    const parentBounds = parent.getBoundingClientRect();
                    const tolerance = 1;
                    const insideParent = bounds.left >= parentBounds.left - tolerance
                        && bounds.top >= parentBounds.top - tolerance
                        && bounds.right <= parentBounds.right + tolerance
                        && bounds.bottom <= parentBounds.bottom + tolerance;

                    if (!insideParent) return false;

                    return [...parent.children]
                        .filter((sibling) => sibling !== element && this.isProtectedLayoutSibling(sibling))
                        .every((sibling) => !this.rectanglesOverlap(bounds, sibling.getBoundingClientRect()));
                });
            },
            isProtectedLayoutSibling(element) {
                if (!(element instanceof HTMLElement)) return false;
                if (element.matches('img, i, hr, .sheet-background, .cover-shade, .cover-line')) return false;

                const bounds = element.getBoundingClientRect();

                return bounds.width > 1 && bounds.height > 1 && getComputedStyle(element).visibility !== 'hidden';
            },
            rectanglesOverlap(first, second) {
                const gap = 1;

                return first.left < second.right - gap
                    && first.right > second.left + gap
                    && first.top < second.bottom - gap
                    && first.bottom > second.top + gap;
            },
            setSelectedFont(event) {
                this.updateSelectedText({ fontFamily: `font:${event.target.value}` });
            },
            setSelectedFontSize(event) {
                this.updateSelectedText({ fontSize: `${Number(event.target.value)}px` });
            },
            setSelectedWeight(event) {
                this.updateSelectedText({ fontWeight: Number(event.target.value) });
            },
            setSelectedColor(property, value) {
                this.updateSelectedText({ [property]: value });
            },
            setSelectedAlignment(alignment) {
                this.updateSelectedText({ textAlign: alignment });
            },
            colorInputValue(value, fallback = '#ffffff') {
                return /^#[0-9a-f]{6}$/i.test(value) ? value : fallback;
            },
            clamp(value, minimum, maximum) {
                return Math.min(Math.max(value, minimum), maximum);
            },
        },
    }).mount('#catalog-app');
})();
