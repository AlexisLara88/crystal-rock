(() => {
    const { createApp } = Vue;
    const presentationEngine = window.CatalogPresentation;

    createApp({
        data() {
            const presentationState = presentationEngine.createPresentationState();
            const imageAdjustments = {};

            return {
                presentationState,
                currentPage: 0,
                showAll: false,
                coverVariant: 'editorial',
                activeImageKey: null,
                activeTextSelection: null,
                selectedTextElement: null,
                textDragState: null,
                textBaseDimensions: {},
                layoutWarning: '',
                textWarnings: [],
                imageAdjustments,
                dragState: null,
                panelDragState: null,
                historyPast: [],
                historyFuture: [],
                historyCurrent: JSON.stringify({ presentationState, imageAdjustments }),
                historyTimer: null,
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
            canUndo() {
                return this.historyPast.length > 0 || this.editorSnapshot() !== this.historyCurrent;
            },
            canRedo() {
                return this.historyFuture.length > 0;
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
            document.addEventListener('keydown', this.handleHistoryShortcut);
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
            document.removeEventListener('keydown', this.handleHistoryShortcut);
            window.removeEventListener('resize', this.constrainFloatingEditor);
            if (this.historyTimer) window.clearTimeout(this.historyTimer);
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
            editorSnapshot() {
                const meaningfulImageAdjustments = Object.fromEntries(
                    Object.entries(this.imageAdjustments)
                        .filter(([, adjustment]) => !this.isDefaultImageAdjustment(adjustment)),
                );

                return JSON.stringify({
                    presentationState: this.presentationState,
                    imageAdjustments: meaningfulImageAdjustments,
                });
            },
            isDefaultImageAdjustment(adjustment) {
                return adjustment.zoom === 1
                    && adjustment.x === 0
                    && adjustment.y === 0
                    && adjustment.maskSize === 0.9
                    && adjustment.rotation === 0;
            },
            queueEditorHistory() {
                if (this.historyTimer) window.clearTimeout(this.historyTimer);

                this.historyTimer = window.setTimeout(() => {
                    this.historyTimer = null;
                    this.commitEditorHistory();
                }, 220);
            },
            commitEditorHistory() {
                const nextSnapshot = this.editorSnapshot();

                if (nextSnapshot === this.historyCurrent) return;

                this.historyPast.push(this.historyCurrent);
                if (this.historyPast.length > 50) this.historyPast.shift();
                this.historyCurrent = nextSnapshot;
                this.historyFuture = [];
            },
            flushEditorHistory() {
                if (!this.historyTimer) return;

                window.clearTimeout(this.historyTimer);
                this.historyTimer = null;
                this.commitEditorHistory();
            },
            restoreEditorSnapshot(snapshot) {
                const restored = JSON.parse(snapshot);
                const activeImageLabel = this.activeImageAdjustment?.label ?? 'Imagen de producto';

                this.presentationState = restored.presentationState;
                this.imageAdjustments = restored.imageAdjustments;
                if (this.activeImageKey && !this.imageAdjustments[this.activeImageKey]) {
                    this.ensureImageAdjustment(this.activeImageKey, activeImageLabel);
                }
                this.textBaseDimensions = {};
                this.layoutWarning = '';
                this.$nextTick(() => this.refreshTextWarnings());
            },
            undoEditorChange() {
                this.flushEditorHistory();
                if (!this.historyPast.length) return;

                this.historyFuture.push(this.historyCurrent);
                this.historyCurrent = this.historyPast.pop();
                this.restoreEditorSnapshot(this.historyCurrent);
            },
            redoEditorChange() {
                this.flushEditorHistory();
                if (!this.historyFuture.length) return;

                this.historyPast.push(this.historyCurrent);
                this.historyCurrent = this.historyFuture.pop();
                this.restoreEditorSnapshot(this.historyCurrent);
            },
            handleHistoryShortcut(event) {
                if (!(event.ctrlKey || event.metaKey)) return;
                const target = event.target;
                const isTypingField = target instanceof HTMLElement && (
                    target.matches('textarea, [contenteditable="true"]')
                    || (
                        target instanceof HTMLInputElement
                        && !['range', 'color', 'button'].includes(target.type)
                    )
                );

                if (isTypingField) {
                    return;
                }

                const key = event.key.toLowerCase();

                if (key === 'z' && !event.shiftKey) {
                    event.preventDefault();
                    this.undoEditorChange();
                } else if (key === 'y' || (key === 'z' && event.shiftKey)) {
                    event.preventDefault();
                    this.redoEditorChange();
                }
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
                    role,
                };
                this.selectedTextElement = event.currentTarget;
                this.captureSelectedTextDimensions(event.currentTarget);
                this.prepareFloatingEditor();
                this.startTextDrag(event);
                this.$nextTick(() => this.refreshTextWarnings());
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
                this.queueEditorHistory();
            },
            setImageZoom(event) {
                if (!this.activeImageAdjustment) return;

                this.activeImageAdjustment.zoom = this.clamp(Number(event.target.value), 0.7, 2.4);
                this.queueEditorHistory();
            },
            setMaskSize(event) {
                if (!this.activeImageAdjustment) return;

                this.activeImageAdjustment.maskSize = this.clamp(Number(event.target.value), 0.6, 1);
                this.queueEditorHistory();
            },
            nudgeImage(deltaX, deltaY) {
                if (!this.activeImageAdjustment) return;

                this.activeImageAdjustment.x = this.clamp(this.activeImageAdjustment.x + deltaX, -50, 50);
                this.activeImageAdjustment.y = this.clamp(this.activeImageAdjustment.y + deltaY, -50, 50);
                this.queueEditorHistory();
            },
            rotateImage(degrees) {
                if (!this.activeImageAdjustment) return;

                let rotation = this.activeImageAdjustment.rotation + degrees;

                if (rotation > 180) rotation -= 360;
                if (rotation < -180) rotation += 360;

                this.activeImageAdjustment.rotation = rotation;
                this.queueEditorHistory();
            },
            resetImageAdjustment() {
                if (!this.activeImageAdjustment) return;

                this.flushEditorHistory();
                this.activeImageAdjustment.zoom = 1;
                this.activeImageAdjustment.x = 0;
                this.activeImageAdjustment.y = 0;
                this.activeImageAdjustment.maskSize = 0.9;
                this.activeImageAdjustment.rotation = 0;
                this.queueEditorHistory();
            },
            dockFloatingEditor() {
                const viewportPadding = 12;
                const panelWidth = 340;

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
                const panelWidth = panel?.offsetWidth ?? 340;
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
                this.textWarnings = [];
            },
            deselectAll() {
                this.deselectImage();
                this.deselectText();
            },
            resetSelectedText() {
                if (!this.activeTextSelection) return;

                const dimensionKey = this.selectedTextDimensionKey();

                this.flushEditorHistory();
                presentationEngine.resetRoleStyle(
                    this.presentationState,
                    this.activeTextSelection.templateId,
                    this.activeTextSelection.role,
                );
                delete this.textBaseDimensions[dimensionKey];
                this.layoutWarning = '';
                this.queueEditorHistory();
                this.$nextTick(() => this.refreshTextWarnings());
            },
            resetSelectedVariant() {
                if (!this.activeTextSelection) return;
                if (!window.confirm('¿Restablecer todos los ajustes de esta variante?')) return;

                const { templateId } = this.activeTextSelection;

                this.flushEditorHistory();
                presentationEngine.resetTemplateStyles(this.presentationState, templateId);
                for (const key of Object.keys(this.textBaseDimensions)) {
                    if (key.startsWith(`${templateId}:`)) delete this.textBaseDimensions[key];
                }
                this.layoutWarning = '';
                this.queueEditorHistory();
                this.$nextTick(() => this.refreshTextWarnings());
            },
            resetPresentationTheme() {
                if (!window.confirm('¿Restablecer el tema y todas las variantes?')) return;

                this.flushEditorHistory();
                this.presentationState = presentationEngine.createPresentationState();
                this.textBaseDimensions = {};
                this.layoutWarning = '';
                this.queueEditorHistory();
                this.$nextTick(() => this.refreshTextWarnings());
            },
            updateSelectedText(patch) {
                if (!this.activeTextSelection) return;

                presentationEngine.updateRoleStyle(
                    this.presentationState,
                    this.activeTextSelection.templateId,
                    this.activeTextSelection.role,
                    patch,
                );
                this.queueEditorHistory();
                this.$nextTick(() => this.refreshTextWarnings());
            },
            selectedTextDimensionKey() {
                if (!this.activeTextSelection) return '';

                return `${this.activeTextSelection.templateId}:${this.activeTextSelection.role}`;
            },
            captureSelectedTextDimensions(element) {
                if (!this.activeTextSelection || !element) return null;

                const current = presentationEngine.resolveRoleStyle(
                    this.presentationState,
                    this.activeTextSelection.templateId,
                    this.activeTextSelection.role,
                );
                const key = this.selectedTextDimensionKey();
                const dimensions = {
                    baseWidth: current.baseWidth ?? Math.max(1, element.offsetWidth),
                    baseHeight: current.baseHeight ?? Math.max(1, element.offsetHeight),
                };

                this.textBaseDimensions[key] = dimensions;

                return dimensions;
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
                    hasMoved: false,
                };
            },
            dragText(event) {
                if (!this.textDragState || this.textDragState.pointerId !== event.pointerId) return;

                const clientDeltaX = event.clientX - this.textDragState.startX;
                const clientDeltaY = event.clientY - this.textDragState.startY;

                if (!this.textDragState.hasMoved && Math.hypot(clientDeltaX, clientDeltaY) < 4) return;

                this.textDragState.hasMoved = true;
                event.preventDefault();
                const candidateX = this.textDragState.originX
                    + (clientDeltaX / this.textDragState.scaleX);
                const candidateY = this.textDragState.originY
                    + (clientDeltaY / this.textDragState.scaleY);

                this.applySelectedLayoutPatch({
                    offsetX: Math.round(candidateX),
                    offsetY: Math.round(candidateY),
                });
            },
            endTextDrag(event) {
                if (!this.textDragState || this.textDragState.pointerId !== event.pointerId) return;

                this.textDragState = null;
            },
            nudgeSelectedText(deltaX, deltaY) {
                if (!this.activeTextControlState) return;

                this.applySelectedLayoutPatch({
                    offsetX: this.activeTextControlState.offsetX
                        + (deltaX * this.activeTextControlState.nudgeStep),
                    offsetY: this.activeTextControlState.offsetY
                        + (deltaY * this.activeTextControlState.nudgeStep),
                });
            },
            setSelectedDimension(property, event) {
                if (!this.activeTextControlState || !this.selectedTextElement) return;

                const dimensions = this.textBaseDimensions[this.selectedTextDimensionKey()]
                    ?? this.captureSelectedTextDimensions(this.selectedTextElement);
                this.applySelectedLayoutPatch({
                    ...dimensions,
                    [property]: Number(event.target.value) / 100,
                });
            },
            applySelectedLayoutPatch(patch) {
                if (!this.activeTextSelection) return;

                const { templateId, role } = this.activeTextSelection;
                const previousOverride = presentationEngine.getRoleOverride(
                    this.presentationState,
                    templateId,
                    role,
                );

                this.updateSelectedText(patch);
                this.$nextTick(() => {
                    if (this.selectedLayoutIsValid()) {
                        this.layoutWarning = '';

                        return;
                    }

                    presentationEngine.replaceRoleOverride(
                        this.presentationState,
                        templateId,
                        role,
                        previousOverride,
                    );
                    this.layoutWarning = 'Movimiento bloqueado: el elemento alcanzó el límite de su zona.';
                    this.$nextTick(() => this.refreshTextWarnings());
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
            refreshTextWarnings() {
                if (!this.activeTextSelection) {
                    this.textWarnings = [];
                    return;
                }

                const elements = [...document.querySelectorAll('.editable-text.active-text')];
                const warnings = [];
                const hasOverflow = elements.some((element) => (
                    element.clientWidth > 0
                    && element.clientHeight > 0
                    && (
                        element.scrollWidth > element.clientWidth + 1
                        || element.scrollHeight > element.clientHeight + 1
                    )
                ));

                if (hasOverflow) warnings.push('El texto excede el espacio disponible.');

                const hasLowContrast = elements.some((element) => {
                    const foreground = this.parseCssColor(getComputedStyle(element).color);
                    const background = this.resolveElementBackground(element);

                    if (!foreground || !background) return false;

                    const fontSize = Number.parseFloat(getComputedStyle(element).fontSize);
                    const fontWeight = Number.parseInt(getComputedStyle(element).fontWeight, 10);
                    const threshold = fontSize >= 24 || (fontSize >= 19 && fontWeight >= 700) ? 3 : 4.5;

                    return this.contrastRatio(foreground, background) < threshold;
                });

                if (hasLowContrast) warnings.push('El contraste entre texto y fondo es bajo.');

                this.textWarnings = warnings;
            },
            resolveElementBackground(element) {
                let composite = { r: 0, g: 0, b: 0, a: 0 };
                let current = element;

                while (current instanceof HTMLElement) {
                    const style = getComputedStyle(current);

                    if (style.backgroundImage !== 'none' && composite.a < 0.99) return null;

                    const layer = this.parseCssColor(style.backgroundColor);
                    if (layer && layer.a > 0) composite = this.compositeColors(composite, layer);
                    if (composite.a >= 0.99) return composite;
                    if (current.classList.contains('catalog-sheet')) break;

                    current = current.parentElement;
                }

                return null;
            },
            parseCssColor(value) {
                if (typeof value !== 'string') return null;

                const rgb = value.match(
                    /^rgba?\(\s*([\d.]+)[,\s]+([\d.]+)[,\s]+([\d.]+)(?:\s*[,/]\s*([\d.]+))?\s*\)$/i,
                );

                if (!rgb) return null;

                return {
                    r: Number(rgb[1]),
                    g: Number(rgb[2]),
                    b: Number(rgb[3]),
                    a: rgb[4] === undefined ? 1 : Number(rgb[4]),
                };
            },
            compositeColors(foreground, background) {
                const alpha = foreground.a + background.a * (1 - foreground.a);

                if (alpha === 0) return { r: 0, g: 0, b: 0, a: 0 };

                return {
                    r: (
                        foreground.r * foreground.a
                        + background.r * background.a * (1 - foreground.a)
                    ) / alpha,
                    g: (
                        foreground.g * foreground.a
                        + background.g * background.a * (1 - foreground.a)
                    ) / alpha,
                    b: (
                        foreground.b * foreground.a
                        + background.b * background.a * (1 - foreground.a)
                    ) / alpha,
                    a: alpha,
                };
            },
            contrastRatio(first, second) {
                const luminance = (color) => {
                    const channels = [color.r, color.g, color.b].map((channel) => {
                        const normalized = channel / 255;

                        return normalized <= 0.03928
                            ? normalized / 12.92
                            : ((normalized + 0.055) / 1.055) ** 2.4;
                    });

                    return channels[0] * 0.2126 + channels[1] * 0.7152 + channels[2] * 0.0722;
                };
                const firstLuminance = luminance(first);
                const secondLuminance = luminance(second);

                return (
                    Math.max(firstLuminance, secondLuminance) + 0.05
                ) / (
                    Math.min(firstLuminance, secondLuminance) + 0.05
                );
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
