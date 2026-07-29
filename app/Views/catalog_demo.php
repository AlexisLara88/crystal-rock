<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <meta name="darkreader-lock">
    <meta name="description" content="Muestra visual del generador automático de catálogos Crystal Rock">
    <title>Crystal Rock · Estudio de catálogo</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/catalog-demo.css?v=' . filemtime(FCPATH . 'assets/css/catalog-demo.css')) ?>">
</head>
<body>
<div id="catalog-app" class="studio-shell" v-cloak>
    <header class="studio-header">
        <div class="studio-brand">
            <span class="studio-mark">CR</span>
            <div>
                <p>Crystal Rock</p>
                <span>Estudio de catálogo</span>
            </div>
        </div>

        <div class="studio-status">
            <span class="status-dot"></span>
            Demo funcional · D2
        </div>

        <div class="header-actions">
            <button type="button" class="ghost-button import-button" @click="openImportPanel">
                Cargar Excel/CSV
            </button>
            <label class="theme-select">
                <span>Estilo de portada</span>
                <select v-model="coverVariant">
                    <option value="editorial">Editorial</option>
                    <option value="promotional">Campaña</option>
                    <option value="minimal">Minimal claro</option>
                </select>
            </label>
            <button type="button" class="ghost-button" @click="toggleShowAll">
                {{ showAll ? 'Ver una página' : 'Ver catálogo completo' }}
            </button>
            <button
                type="button"
                class="ghost-button pdf-proof-button"
                :disabled="pdfExporting"
                @click="exportCurrentPdf"
            >
                {{ pdfExportButtonLabel }}
            </button>
            <span v-if="pdfExportError" class="pdf-export-error" role="alert">{{ pdfExportError }}</span>
        </div>
    </header>

    <main class="studio-main">
        <aside class="studio-sidebar">
            <div class="sidebar-intro">
                <span class="eyebrow">Sistema visual</span>
                <h1>Una identidad, {{ pages.length }} composiciones.</h1>
                <p>La estructura decide cómo acomodar el contenido; el usuario solo elige y corrige datos.</p>
            </div>

            <nav class="page-nav" aria-label="Plantillas del catálogo">
                <button
                    v-for="(page, index) in pages"
                    :key="page.id"
                    type="button"
                    :class="{ active: currentPage === index }"
                    @click="selectPage(index)"
                >
                    <span class="page-number">{{ String(index + 1).padStart(2, '0') }}</span>
                    <span>
                        <strong>{{ page.label }}</strong>
                        <small>{{ page.description }}</small>
                    </span>
                    <span class="page-arrow">↗</span>
                </button>
            </nav>

            <div class="sidebar-note">
                <span>Regla activa</span>
                <strong>El destacado no se repite.</strong>
                <p>Los productos restantes se agrupan automáticamente en bloques de hasta cuatro.</p>
            </div>
        </aside>

        <section class="preview-panel">
            <div class="preview-toolbar">
                <div>
                    <span class="eyebrow">Vista previa A4</span>
                    <strong>{{ showAll ? pages.length + ' páginas' : pages[currentPage].label }}</strong>
                    <small class="catalog-source-summary">{{ catalogSourceSummary }}</small>
                </div>

                <div class="preview-controls">
                    <button type="button" @click="previousPage" :disabled="showAll || currentPage === 0" aria-label="Página anterior">←</button>
                    <span>{{ currentPage + 1 }} / {{ pages.length }}</span>
                    <button type="button" @click="nextPage" :disabled="showAll || currentPage === pages.length - 1" aria-label="Página siguiente">→</button>
                </div>
            </div>

            <div v-if="compositionNotice" class="composition-notice" role="status">
                <span>✓</span>
                <strong>{{ compositionNotice }}</strong>
                <button type="button" aria-label="Cerrar aviso" @click="compositionNotice = ''">×</button>
            </div>

            <div :class="['preview-scroll', { 'all-pages': showAll }]">
                <article
                    v-for="(page, index) in visiblePages"
                    :key="page.id"
                    class="catalog-sheet-wrap"
                >
                    <div :class="['catalog-sheet', 'sheet-' + page.type, page.type === 'cover' ? 'cover-' + coverVariant : '']">
                        <template v-if="page.type === 'cover'">
                            <img class="sheet-background" :src="asset('cover-wine.png')" alt="">
                            <div class="cover-shade"></div>
                            <div v-if="coverVariant !== 'minimal'" class="cover-promo">
                                <small
                                    v-if="coverVariant === 'promotional'"
                                    :class="textSelectionClass(coverTemplateId(), 'promotionBadge')"
                                    :style="textStyle(coverTemplateId(), 'promotionBadge')"
                                    @pointerdown="selectText(coverTemplateId(), 'promotionBadge', $event)"
                                >¡Nuevo!</small>
                                <span
                                    :class="textSelectionClass(coverTemplateId(), 'promotionText')"
                                    :style="textStyle(coverTemplateId(), 'promotionText')"
                                    @pointerdown="selectText(coverTemplateId(), 'promotionText', $event)"
                                >Hacé tu compra por<br>la <b>web</b> y obtené un</span>
                                <strong
                                    :class="textSelectionClass(coverTemplateId(), 'promotionValue')"
                                    :style="textStyle(coverTemplateId(), 'promotionValue')"
                                    @pointerdown="selectText(coverTemplateId(), 'promotionValue', $event)"
                                >10% OFF</strong>
                            </div>
                            <div
                                class="date-pill"
                                :class="textSelectionClass(coverTemplateId(), 'date')"
                                :style="textStyle(coverTemplateId(), 'date')"
                                @pointerdown="selectText(coverTemplateId(), 'date', $event)"
                            >{{ page.updatedLabel || 'ACTUALIZADO: 28/07/26' }}</div>
                            <div class="cover-line line-one"></div>
                            <div class="cover-line line-two"></div>
                            <div class="cover-title">
                                <span
                                    class="catalog-wordmark"
                                    :class="textSelectionClass(coverTemplateId(), 'wordmark')"
                                    :style="textStyle(coverTemplateId(), 'wordmark')"
                                    @pointerdown="selectText(coverTemplateId(), 'wordmark', $event)"
                                >CRYSTALROCK</span>
                                <h2
                                    :class="textSelectionClass(coverTemplateId(), 'coverTitle')"
                                    :style="textStyle(coverTemplateId(), 'coverTitle')"
                                    @pointerdown="selectText(coverTemplateId(), 'coverTitle', $event)"
                                >{{ page.title || 'Cristalería' }}</h2>
                                <p
                                    :class="textSelectionClass(coverTemplateId(), 'coverSubtitle')"
                                    :style="textStyle(coverTemplateId(), 'coverSubtitle')"
                                    @pointerdown="selectText(coverTemplateId(), 'coverSubtitle', $event)"
                                >{{ page.subtitle || 'Calidad real para casas reales' }}</p>
                            </div>
                        </template>

                        <template v-else-if="page.type === 'featured'">
                            <div class="featured-shell">
                                <div class="featured-hero">
                                    <img :src="asset('feature-wine.jpg')" alt="">
                                    <div
                                        class="category-ribbon"
                                        :class="textSelectionClass('featured', 'category')"
                                        :style="textStyle('featured', 'category')"
                                        @pointerdown="selectText('featured', 'category', $event)"
                                    >{{ (page.category || 'CRISTALERÍA').toLocaleUpperCase('es') }}</div>
                                    <div
                                        class="featured-label"
                                        :class="textSelectionClass('featured', 'featuredLabel')"
                                        :style="textStyle('featured', 'featuredLabel')"
                                        @pointerdown="selectText('featured', 'featuredLabel', $event)"
                                    >Producto<br>destacado</div>
                                </div>
                                <div class="featured-product">
                                    <div
                                        class="featured-name"
                                        :class="textSelectionClass('featured', 'productName')"
                                        :style="textStyle('featured', 'productName')"
                                        @pointerdown="selectText('featured', 'productName', $event)"
                                    >{{ featuredForPage(page).name || 'Copas Gin Tonic 590 ML' }}</div>
                                    <div class="featured-cutout-slot">
                                        <div
                                            :class="['featured-cutout', 'image-mask', { active: activeImageKey === productImageKey(page, featuredForPage(page), 'featured') }]"
                                            :style="maskStyle(productImageKey(page, featuredForPage(page), 'featured'))"
                                            @pointerdown="startImageDrag(productImageKey(page, featuredForPage(page), 'featured'), featuredForPage(page).name, $event)"
                                            @pointermove="dragImage"
                                            @pointerup="endImageDrag"
                                            @pointercancel="endImageDrag"
                                        >
                                            <img
                                                :src="productImageSource(featuredForPage(page), 'glass-gin.png')"
                                                :alt="featuredForPage(page).name"
                                                :style="imageStyle(productImageKey(page, featuredForPage(page), 'featured'))"
                                                draggable="false"
                                            >
                                        </div>
                                    </div>
                                    <dl
                                        class="product-specs featured-specs"
                                        :class="textSelectionClass('featured', 'productSpecs')"
                                        :style="textStyle('featured', 'productSpecs')"
                                        @pointerdown="selectText('featured', 'productSpecs', $event)"
                                    >
                                        <template v-for="spec in featuredForPage(page).specs" :key="spec[0]">
                                            <dt>{{ spec[0] }}</dt>
                                            <dd>{{ spec[1] }}</dd>
                                        </template>
                                    </dl>
                                    <div class="featured-commerce">
                                        <div
                                            class="featured-code"
                                            :class="textSelectionClass('featured', 'productCode')"
                                            :style="textStyle('featured', 'productCode')"
                                            @pointerdown="selectText('featured', 'productCode', $event)"
                                        ><b>Cod.</b> {{ featuredForPage(page).code }}</div>
                                        <div
                                            class="featured-price"
                                            :class="textSelectionClass('featured', 'productPrice')"
                                            :style="textStyle('featured', 'productPrice')"
                                            @pointerdown="selectText('featured', 'productPrice', $event)"
                                        >Ud. {{ featuredForPage(page).price }}</div>
                                    </div>
                                </div>
                            </div>
                            <footer class="sheet-footer light-footer">
                                <span
                                    class="catalog-wordmark footer-wordmark"
                                    :class="textSelectionClass('featured', 'footerWordmark')"
                                    :style="textStyle('featured', 'footerWordmark')"
                                    @pointerdown="selectText('featured', 'footerWordmark', $event)"
                                >CRYSTALROCK</span>
                                <i></i>
                                <span
                                    :class="textSelectionClass('featured', 'footerTagline')"
                                    :style="textStyle('featured', 'footerTagline')"
                                    @pointerdown="selectText('featured', 'footerTagline', $event)"
                                >PARA CASAS REALES</span>
                            </footer>
                        </template>

                        <template v-else-if="page.type === 'grid'">
                            <div :class="['product-grid', 'product-grid-' + page.count]">
                                <section
                                    v-for="product in gridProductsForPage(page)"
                                    :key="productImageKey(page, product)"
                                    class="product-card"
                                >
                                    <div class="product-copy">
                                        <div
                                            class="product-title"
                                            :class="textSelectionClass(gridTemplateId(page.count), 'productName')"
                                            :style="textStyle(gridTemplateId(page.count), 'productName')"
                                            @pointerdown="selectText(gridTemplateId(page.count), 'productName', $event)"
                                        >{{ product.name }}</div>
                                        <div class="product-data">
                                            <dl
                                                class="product-specs"
                                                :class="textSelectionClass(gridTemplateId(page.count), 'productSpecs')"
                                                :style="textStyle(gridTemplateId(page.count), 'productSpecs')"
                                                @pointerdown="selectText(gridTemplateId(page.count), 'productSpecs', $event)"
                                            >
                                                <template v-for="spec in product.specs" :key="spec[0]">
                                                    <dt>{{ spec[0] }}</dt>
                                                    <dd>{{ spec[1] }}</dd>
                                                </template>
                                            </dl>
                                            <div class="product-commerce">
                                                <div
                                                    class="product-code"
                                                    :class="textSelectionClass(gridTemplateId(page.count), 'productCode')"
                                                    :style="textStyle(gridTemplateId(page.count), 'productCode')"
                                                    @pointerdown="selectText(gridTemplateId(page.count), 'productCode', $event)"
                                                ><b>Cod.</b> {{ product.code }}</div>
                                                <div
                                                    class="product-price"
                                                    :class="textSelectionClass(gridTemplateId(page.count), 'productPrice')"
                                                    :style="textStyle(gridTemplateId(page.count), 'productPrice')"
                                                    @pointerdown="selectText(gridTemplateId(page.count), 'productPrice', $event)"
                                                >Ud. {{ product.price }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="product-image-slot">
                                        <div
                                            :class="['product-image', 'image-mask', { active: activeImageKey === productImageKey(page, product) }]"
                                            :style="maskStyle(productImageKey(page, product))"
                                            @pointerdown="startImageDrag(productImageKey(page, product), product.name, $event)"
                                            @pointermove="dragImage"
                                            @pointerup="endImageDrag"
                                            @pointercancel="endImageDrag"
                                        >
                                            <img
                                                :src="productImageSource(product)"
                                                :alt="product.name"
                                                :style="imageStyle(productImageKey(page, product))"
                                                draggable="false"
                                            >
                                        </div>
                                    </div>
                                </section>
                            </div>
                            <footer class="sheet-footer light-footer grid-footer">
                                <span
                                    class="catalog-wordmark footer-wordmark"
                                    :class="textSelectionClass(gridTemplateId(page.count), 'footerWordmark')"
                                    :style="textStyle(gridTemplateId(page.count), 'footerWordmark')"
                                    @pointerdown="selectText(gridTemplateId(page.count), 'footerWordmark', $event)"
                                >CRYSTALROCK</span>
                                <i></i>
                                <span
                                    :class="textSelectionClass(gridTemplateId(page.count), 'footerTagline')"
                                    :style="textStyle(gridTemplateId(page.count), 'footerTagline')"
                                    @pointerdown="selectText(gridTemplateId(page.count), 'footerTagline', $event)"
                                >PARA CASAS REALES</span>
                            </footer>
                        </template>

                        <template v-else-if="page.type === 'back'">
                            <img class="sheet-background" :src="asset('back-community.png')" alt="">
                            <div class="back-shade"></div>
                            <div class="back-content">
                                <span
                                    class="catalog-wordmark back-wordmark"
                                    :class="textSelectionClass('back', 'wordmark')"
                                    :style="textStyle('back', 'wordmark')"
                                    @pointerdown="selectText('back', 'wordmark', $event)"
                                >CRYSTALROCK</span>
                                <div class="order-card">
                                    <h2
                                        :class="textSelectionClass('back', 'backHeading')"
                                        :style="textStyle('back', 'backHeading')"
                                        @pointerdown="selectText('back', 'backHeading', $event)"
                                    >¿Cómo hacer tu pedido?</h2>
                                    <hr>
                                    <strong
                                        :class="textSelectionClass('back', 'backChannel')"
                                        :style="textStyle('back', 'backChannel')"
                                        @pointerdown="selectText('back', 'backChannel', $event)"
                                    >Por nuestra web</strong>
                                    <a
                                        href="https://www.crystalrock.com.ar"
                                        :class="textSelectionClass('back', 'backLink')"
                                        :style="textStyle('back', 'backLink')"
                                        @pointerdown="selectText('back', 'backLink', $event)"
                                        @click.prevent
                                    >www.crystalrock.com.ar <span>↗</span></a>
                                    <p
                                        :class="textSelectionClass('back', 'backNote')"
                                        :style="textStyle('back', 'backNote')"
                                        @pointerdown="selectText('back', 'backNote', $event)"
                                    >¡Obtenés un 10% OFF!</p>
                                    <strong
                                        :class="textSelectionClass('back', 'backChannel')"
                                        :style="textStyle('back', 'backChannel')"
                                        @pointerdown="selectText('back', 'backChannel', $event)"
                                    >Por WhatsApp</strong>
                                    <a
                                        href="#"
                                        :class="textSelectionClass('back', 'backLink')"
                                        :style="textStyle('back', 'backLink')"
                                        @pointerdown="selectText('back', 'backLink', $event)"
                                        @click.prevent
                                    >Atención personalizada <span>↗</span></a>
                                </div>
                                <div class="community-cta">
                                    <h3
                                        :class="textSelectionClass('back', 'communityHeading')"
                                        :style="textStyle('back', 'communityHeading')"
                                        @pointerdown="selectText('back', 'communityHeading', $event)"
                                    >¡Sumate a nuestra<br>comunidad en las redes!</h3>
                                    <a
                                        href="#"
                                        :class="textSelectionClass('back', 'communityLink')"
                                        :style="textStyle('back', 'communityLink')"
                                        @pointerdown="selectText('back', 'communityLink', $event)"
                                        @click.prevent
                                    >Instagram</a>
                                </div>
                            </div>
                        </template>
                    </div>
                    <p class="page-caption">{{ showAll ? index + 1 : currentPage + 1 }} · {{ page.label }}</p>
                </article>
            </div>
        </section>
    </main>

    <aside
        v-if="activeImageAdjustment"
        class="image-editor floating-image-editor floating-element-editor"
        :style="floatingEditorStyle"
        aria-live="polite"
        @pointerdown.stop
        @click.stop
    >
        <button type="button" class="close-image-editor" @click="deselectImage" aria-label="Cerrar ajuste de imagen">×</button>
        <div
            class="floating-editor-handle"
            title="Arrastrar panel"
            @pointerdown="startFloatingEditorDrag"
        >
            <span class="floating-editor-grip" aria-hidden="true">⠿</span>
            <div>
                <span class="image-editor-kicker">Ajuste de imagen</span>
                <strong>{{ activeImageAdjustment.label }}</strong>
            </div>
        </div>
        <div class="history-controls" aria-label="Historial de edición">
            <button type="button" :disabled="!canUndo" @click="undoEditorChange" title="Ctrl/Cmd + Z">↶ Deshacer</button>
            <button type="button" :disabled="!canRedo" @click="redoEditorChange" title="Ctrl/Cmd + Shift + Z">Rehacer ↷</button>
        </div>
        <p>Arrastrá la imagen dentro de la máscara o usá los controles.</p>

        <label class="zoom-control">
            <span>Zoom</span>
            <input
                type="range"
                min="0.7"
                max="2.4"
                step="0.05"
                :value="activeImageAdjustment.zoom"
                @input="setImageZoom"
            >
            <output>{{ Math.round(activeImageAdjustment.zoom * 100) }}%</output>
        </label>

        <label class="zoom-control mask-size-control">
            <span>Marco</span>
            <input
                type="range"
                min="0.6"
                max="1"
                step="0.05"
                :value="activeImageAdjustment.maskSize"
                @input="setMaskSize"
            >
            <output>{{ Math.round(activeImageAdjustment.maskSize * 100) }}%</output>
        </label>

        <div class="position-controls" aria-label="Posición de la imagen">
            <button type="button" @click="nudgeImage(0, -8)" aria-label="Mover arriba">↑</button>
            <button type="button" @click="nudgeImage(-8, 0)" aria-label="Mover a la izquierda">←</button>
            <button type="button" @click="resetImageAdjustment" aria-label="Restablecer ajuste">●</button>
            <button type="button" @click="nudgeImage(8, 0)" aria-label="Mover a la derecha">→</button>
            <button type="button" @click="nudgeImage(0, 8)" aria-label="Mover abajo">↓</button>
        </div>

        <div class="rotation-controls" aria-label="Rotación de la imagen">
            <button type="button" @click="rotateImage(-15)" aria-label="Girar 15 grados a la izquierda">↶</button>
            <span>Giro <output>{{ Math.round(activeImageAdjustment.rotation) }}°</output></span>
            <button type="button" @click="rotateImage(15)" aria-label="Girar 15 grados a la derecha">↷</button>
        </div>
        <small>Los controles y la selección no aparecen en el PDF.</small>
    </aside>

    <aside
        v-if="activeTextInspector"
        class="image-editor floating-image-editor floating-element-editor text-element-editor"
        :style="floatingEditorStyle"
        aria-live="polite"
        @pointerdown.stop
        @click.stop
    >
        <button type="button" class="close-image-editor" @click="deselectText" aria-label="Cerrar selección de texto">×</button>
        <div
            class="floating-editor-handle"
            title="Arrastrar panel"
            @pointerdown="startFloatingEditorDrag"
        >
            <span class="floating-editor-grip" aria-hidden="true">⠿</span>
            <div>
                <span class="image-editor-kicker">Elemento de texto</span>
                <strong>{{ activeTextInspector.label }}</strong>
            </div>
        </div>
        <div class="history-controls" aria-label="Historial de edición">
            <button type="button" :disabled="!canUndo" @click="undoEditorChange" title="Ctrl/Cmd + Z">↶ Deshacer</button>
            <button type="button" :disabled="!canRedo" @click="redoEditorChange" title="Ctrl/Cmd + Shift + Z">Rehacer ↷</button>
        </div>

        <div class="text-style-controls">
            <label class="inspector-field">
                <span>Tipografía</span>
                <select :value="activeTextControlState.fontKey" @change="setSelectedFont">
                    <option v-for="font in fontOptions" :key="font.value" :value="font.value">
                        {{ font.label }}
                    </option>
                </select>
            </label>

            <label class="inspector-field font-size-field">
                <span>Tamaño</span>
                <input
                    type="range"
                    :min="activeTextControlState.minFontSize"
                    :max="activeTextControlState.maxFontSize"
                    step="1"
                    :value="activeTextControlState.fontSize"
                    @input="setSelectedFontSize"
                >
                <output>{{ activeTextControlState.fontSize }} px</output>
            </label>

            <div class="layout-editor">
                <div class="layout-editor-heading">
                    <span>Posición dentro de la zona</span>
                    <output>X {{ activeTextControlState.offsetX }} · Y {{ activeTextControlState.offsetY }}</output>
                </div>
                <div class="text-position-controls" aria-label="Ajuste fino de posición">
                    <button type="button" @click="nudgeSelectedText(0, -1)" aria-label="Mover arriba">↑</button>
                    <button type="button" @click="nudgeSelectedText(-1, 0)" aria-label="Mover a la izquierda">←</button>
                    <button type="button" class="position-center" disabled aria-label="Posición actual">●</button>
                    <button type="button" @click="nudgeSelectedText(1, 0)" aria-label="Mover a la derecha">→</button>
                    <button type="button" @click="nudgeSelectedText(0, 1)" aria-label="Mover abajo">↓</button>
                </div>
                <small>
                    Paso de {{ activeTextControlState.nudgeStep }} px. También podés arrastrar directamente.
                </small>
            </div>

            <label class="inspector-field dimension-field">
                <span>Ancho del bloque</span>
                <input
                    type="range"
                    :min="activeTextControlState.minWidthPercent"
                    :max="activeTextControlState.maxWidthPercent"
                    step="1"
                    :value="activeTextControlState.widthPercent"
                    @input="setSelectedDimension('widthScale', $event)"
                >
                <output>{{ activeTextControlState.widthPercent }}%</output>
            </label>

            <label class="inspector-field dimension-field">
                <span>Alto del bloque</span>
                <input
                    type="range"
                    :min="activeTextControlState.minHeightPercent"
                    :max="activeTextControlState.maxHeightPercent"
                    step="1"
                    :value="activeTextControlState.heightPercent"
                    @input="setSelectedDimension('heightScale', $event)"
                >
                <output>{{ activeTextControlState.heightPercent }}%</output>
            </label>

            <div class="inspector-row">
                <label class="inspector-field">
                    <span>Peso</span>
                    <select :value="activeTextControlState.current.fontWeight" @change="setSelectedWeight">
                        <option value="300">Light</option>
                        <option value="400">Regular</option>
                        <option value="600">Semibold</option>
                        <option value="700">Bold</option>
                        <option value="800">Extra bold</option>
                    </select>
                </label>

                <div class="inspector-field">
                    <span>Alineación</span>
                    <div class="alignment-controls">
                        <button
                            v-for="alignment in ['left', 'center', 'right']"
                            :key="alignment"
                            type="button"
                            :class="{ active: activeTextControlState.current.textAlign === alignment }"
                            @click="setSelectedAlignment(alignment)"
                        >
                            {{ alignment === 'left' ? '≡←' : alignment === 'center' ? '≡' : '→≡' }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="color-editor">
                <div class="color-editor-heading">
                    <span>Color de texto</span>
                    <input
                        type="color"
                        :value="colorInputValue(activeTextControlState.current.color, '#29332f')"
                        aria-label="Color de texto personalizado"
                        @input="setSelectedColor('color', $event.target.value)"
                    >
                </div>
                <div class="color-swatches">
                    <button
                        v-for="color in textColorPalette"
                        :key="'text-' + color.name"
                        type="button"
                        :title="color.name"
                        :aria-label="'Usar ' + color.name + ' como color de texto'"
                        :style="{ backgroundColor: color.value }"
                        @click="setSelectedColor('color', color.value)"
                    ></button>
                </div>
            </div>

            <div class="color-editor">
                <div class="color-editor-heading">
                    <span>Color de fondo</span>
                    <input
                        type="color"
                        :value="colorInputValue(activeTextControlState.current.backgroundColor)"
                        aria-label="Color de fondo personalizado"
                        @input="setSelectedColor('backgroundColor', $event.target.value)"
                    >
                </div>
                <div class="color-swatches">
                    <button
                        v-for="color in textColorPalette"
                        :key="'background-' + color.name"
                        type="button"
                        :title="color.name"
                        :aria-label="'Usar ' + color.name + ' como fondo'"
                        :style="{ backgroundColor: color.value }"
                        @click="setSelectedColor('backgroundColor', color.value)"
                    ></button>
                    <button
                        type="button"
                        class="transparent-swatch"
                        title="Sin fondo"
                        aria-label="Quitar color de fondo"
                        @click="setSelectedColor('backgroundColor', 'transparent')"
                    >×</button>
                </div>
            </div>
        </div>

        <p v-if="layoutWarning" class="layout-warning" role="alert">{{ layoutWarning }}</p>
        <p
            v-for="warning in textWarnings"
            :key="warning"
            class="layout-warning quality-warning"
            role="status"
        >{{ warning }}</p>

        <div class="text-reset-actions">
            <button type="button" class="text-reset-button element-reset" @click="resetSelectedText">
                Restablecer elemento
            </button>
            <button type="button" class="text-reset-button" @click="resetSelectedVariant">
                Restablecer variante
            </button>
            <button type="button" class="text-reset-button theme-reset" @click="resetPresentationTheme">
                Restablecer tema
            </button>
        </div>
        <small>La selección y este panel no aparecen en el PDF.</small>
    </aside>

    <div
        v-if="importPanelOpen"
        class="import-overlay"
        role="presentation"
        @pointerdown.self="closeImportPanel"
    >
        <section class="import-dialog" role="dialog" aria-modal="true" aria-labelledby="import-dialog-title">
            <header class="import-dialog-header">
                <div>
                    <span class="eyebrow">D2 · Importación</span>
                    <h2 id="import-dialog-title">Revisar datos antes de diseñar</h2>
                    <p>Este paso solo lee y valida el archivo. Todavía no reemplaza los productos del catálogo.</p>
                </div>
                <button
                    type="button"
                    class="import-close"
                    aria-label="Cerrar importación"
                    :disabled="importingCatalog"
                    @click="closeImportPanel"
                >×</button>
            </header>

            <form
                :class="['import-form', { 'import-form-compact': importResult || importError }]"
                @submit.prevent="importCatalogFile"
            >
                <label class="import-dropzone">
                    <input
                        ref="catalogFileInput"
                        type="file"
                        name="catalogFile"
                        accept=".xlsx,.csv"
                        @change="selectCatalogFile"
                    >
                    <span class="import-file-icon">↥</span>
                    <strong>{{ selectedCatalogFile ? selectedCatalogFile.name : 'Elegir Excel o CSV' }}</strong>
                    <small>Hasta 5 MB · La demo no guarda el archivo</small>
                </label>
                <button
                    type="submit"
                    class="import-submit"
                    :disabled="!selectedCatalogFile || importingCatalog"
                >
                    {{ importingCatalog ? 'Leyendo archivo…' : 'Analizar archivo' }}
                </button>
            </form>

            <p v-if="importError" class="import-message import-message-error" role="alert">
                {{ importError }}
            </p>

            <template v-if="importResult">
                <div class="import-summary">
                    <div>
                        <span>Archivo</span>
                        <strong>{{ importedFilename }}</strong>
                    </div>
                    <div>
                        <span>Hoja activa</span>
                        <strong>{{ importResult.activeSheet }}</strong>
                    </div>
                    <div>
                        <span>Productos</span>
                        <strong>{{ importResult.summary.totalRows }}</strong>
                    </div>
                    <div class="summary-warning">
                        <span>Avisos de origen</span>
                        <strong>{{ importResult.summary.warningRows }}</strong>
                    </div>
                    <div :class="{ 'summary-error': importResult.summary.errorRows > 0 }">
                        <span>Errores</span>
                        <strong>{{ importResult.summary.errorRows }}</strong>
                    </div>
                </div>

                <div v-if="importResult.globalWarnings.length" class="import-notices">
                    <p v-for="warning in importResult.globalWarnings" :key="warning">{{ warning }}</p>
                </div>

                <section class="image-import-section">
                    <div class="image-import-heading">
                        <div>
                            <span class="eyebrow">2 · Vincular imágenes</span>
                            <h3>Relacionar cada archivo con @Image</h3>
                            <p>Elegí un ZIP o varias imágenes JPG, PNG o WebP. La coincidencia se realiza por nombre.</p>
                        </div>
                        <span v-if="imageResult" class="image-review-badge">
                            {{ imageReviewSummary.resolvedRows }}/{{ imageReviewSummary.activeRows }} resueltas
                        </span>
                    </div>

                    <form class="image-import-form" @submit.prevent="matchCatalogImages">
                        <label class="image-import-picker">
                            <input
                                ref="catalogImagesInput"
                                type="file"
                                accept=".zip,.jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                multiple
                                @change="selectCatalogImages"
                            >
                            <span class="import-file-icon">▧</span>
                            <strong>{{ selectedImageFilesLabel }}</strong>
                            <small>ZIP hasta 25 MB · o hasta 20 imágenes de 12 MB cada una</small>
                        </label>
                        <button
                            type="submit"
                            class="import-submit image-match-submit"
                            :disabled="!selectedImageFiles.length || imageMatching"
                        >
                            {{ imageMatching ? 'Revisando imágenes…' : 'Vincular imágenes' }}
                        </button>
                    </form>

                    <p v-if="imageError" class="image-import-error" role="alert">{{ imageError }}</p>

                    <div v-if="imageResult" class="image-match-summary">
                        <div>
                            <span>Archivos útiles</span>
                            <strong>{{ imageResult.summary.usableFiles }}</strong>
                        </div>
                        <div class="summary-success">
                            <span>Resueltas</span>
                            <strong>{{ imageReviewSummary.resolvedRows }}</strong>
                        </div>
                        <div :class="{ 'summary-error': imageReviewSummary.missingRows > 0 }">
                            <span>Faltantes</span>
                            <strong>{{ imageReviewSummary.missingRows }}</strong>
                        </div>
                        <div :class="{ 'summary-error': imageReviewSummary.duplicateRows > 0 }">
                            <span>Duplicadas</span>
                            <strong>{{ imageReviewSummary.duplicateRows }}</strong>
                        </div>
                        <div :class="{ 'summary-warning': imageResult.summary.unusedFiles > 0 }">
                            <span>Sobrantes</span>
                            <strong>{{ imageResult.summary.unusedFiles }}</strong>
                        </div>
                        <div :class="{ 'summary-warning': imageReviewSummary.excludedRows > 0 }">
                            <span>Excluidos</span>
                            <strong>{{ imageReviewSummary.excludedRows }}</strong>
                        </div>
                    </div>

                    <div
                        v-if="imageResult && (imageResult.unusedFiles.length || imageResult.rejectedFiles.length)"
                        class="image-file-notices"
                    >
                        <p v-if="imageResult.unusedFiles.length">
                            <strong>Sin utilizar:</strong>
                            {{ unusedImageLabels() }}
                        </p>
                        <p v-for="file in imageResult.rejectedFiles" :key="file.name + file.reason">
                            <strong>{{ file.name }}:</strong> {{ file.reason }}
                        </p>
                    </div>
                </section>

                <section class="data-review-section">
                    <div class="data-review-heading">
                        <div>
                            <span class="eyebrow">3 · Corregir y categorizar</span>
                            <h3>Preparar los datos para la composición</h3>
                            <p>Las categorías iniciales son sugerencias editables. Corregí cada fila o aplicá una categoría en grupo.</p>
                        </div>
                        <span class="data-review-badge">
                            {{ importReadiness.readyRows }}/{{ importReadiness.activeRows }} listas
                        </span>
                    </div>

                    <div class="category-manager">
                        <div class="category-quick-assign">
                            <div class="category-quick-heading">
                                <strong>Asignar categoría</strong>
                                <span>
                                    {{ selectedImportRowCount
                                        ? selectedImportRowCount + ' producto' + (selectedImportRowCount === 1 ? '' : 's') + ' seleccionado' + (selectedImportRowCount === 1 ? '' : 's')
                                        : 'Seleccioná productos en la columna Fila' }}
                                </span>
                            </div>
                            <div class="category-list" aria-label="Categorías disponibles">
                                <button
                                    v-for="category in catalogCategories"
                                    :key="category"
                                    type="button"
                                    :disabled="selectedImportRowCount === 0"
                                    @click="assignCategoryToSelection(category)"
                                >
                                    {{ category }}
                                </button>
                            </div>
                        </div>
                        <form class="category-create" @submit.prevent="addCatalogCategory">
                            <label for="new-catalog-category">Crear otra categoría</label>
                            <input
                                id="new-catalog-category"
                                v-model="newCategoryName"
                                type="text"
                                maxlength="60"
                                placeholder="Ej. Regalos empresariales"
                            >
                            <button type="submit" :disabled="!newCategoryName.trim()">
                                {{ selectedImportRowCount ? 'Crear y asignar' : 'Crear categoría' }}
                            </button>
                            <small>
                                {{ selectedImportRowCount
                                    ? 'Se asignará inmediatamente a la selección.'
                                    : 'Después podrás asignarla seleccionando productos.' }}
                            </small>
                        </form>
                    </div>
                </section>

                <div class="import-mapping">
                    <span class="eyebrow">Mapeo detectado</span>
                    <div>
                        <span
                            v-for="header in importResult.headers"
                            :key="header.column"
                            :class="{ unknown: !header.field }"
                        >
                            {{ header.label }} → {{ header.field ? importFieldLabel(header.field) : 'Sin usar' }}
                        </span>
                    </div>
                </div>

                <div class="import-table-wrap">
                    <table class="import-table">
                        <thead>
                            <tr>
                                <th>
                                    <label class="select-all-rows">
                                        <input
                                            type="checkbox"
                                            :checked="allActiveImportRowsSelected"
                                            aria-label="Seleccionar todas las filas activas"
                                            @change="toggleAllImportRows"
                                        >
                                        <span>Fila</span>
                                    </label>
                                </th>
                                <th>Producto</th>
                                <th>Código</th>
                                <th>Precio</th>
                                <th>Categoría</th>
                                <th>Vista previa</th>
                                <th>Estado</th>
                                <th>Imagen (@Image)</th>
                                <th>Resolver</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="row in importResult.rows"
                                :key="row.sourceRow"
                                :class="{ 'import-row-excluded': isRowExcluded(row) }"
                            >
                                <td>
                                    <label class="row-selector">
                                        <input
                                            type="checkbox"
                                            :checked="Boolean(selectedImportRows[String(row.sourceRow)])"
                                            :disabled="isRowExcluded(row)"
                                            :aria-label="'Seleccionar fila ' + row.sourceRow"
                                            @change="toggleImportRowSelection(row)"
                                        >
                                        <span>{{ row.sourceRow }}</span>
                                    </label>
                                </td>
                                <td>
                                    <input
                                        v-model="importEditsByRow[String(row.sourceRow)].name"
                                        class="table-edit table-edit-name"
                                        type="text"
                                        :disabled="isRowExcluded(row)"
                                        aria-label="Nombre del producto"
                                        @input="invalidateApprovedCatalog"
                                    >
                                </td>
                                <td>
                                    <input
                                        v-model="importEditsByRow[String(row.sourceRow)].code"
                                        class="table-edit table-edit-code"
                                        type="text"
                                        :disabled="isRowExcluded(row)"
                                        aria-label="Código del producto"
                                        @input="invalidateApprovedCatalog"
                                    >
                                </td>
                                <td>
                                    <input
                                        v-model="importEditsByRow[String(row.sourceRow)].price"
                                        class="table-edit table-edit-price"
                                        type="text"
                                        inputmode="decimal"
                                        :disabled="isRowExcluded(row)"
                                        aria-label="Precio del producto"
                                        @input="invalidateApprovedCatalog"
                                        @blur="normalizeEditedPrice(row)"
                                    >
                                </td>
                                <td>
                                    <select
                                        v-model="importEditsByRow[String(row.sourceRow)].category"
                                        class="table-edit table-edit-category"
                                        :disabled="isRowExcluded(row)"
                                        aria-label="Categoría del producto"
                                        @change="invalidateApprovedCatalog"
                                    >
                                        <option value="">Sin categoría</option>
                                        <option v-for="category in catalogCategories" :key="category" :value="category">
                                            {{ category }}
                                        </option>
                                    </select>
                                </td>
                                <td>
                                    <img
                                        v-if="imageMatchForRow(row)?.image"
                                        class="import-image-preview"
                                        :src="imageMatchForRow(row).image.preview"
                                        :alt="'Vista previa de ' + row.values.name"
                                    >
                                    <span v-else class="import-image-placeholder">Sin imagen</span>
                                </td>
                                <td>
                                    <span :class="['row-status', 'row-status-' + combinedImportStatus(row)]">
                                        {{ importStatusLabel(combinedImportStatus(row)) }}
                                    </span>
                                    <small>
                                        {{ importRowIssues(row) }}
                                    </small>
                                </td>
                                <td>
                                    <strong class="image-reference">{{ row.values.image || '—' }}</strong>
                                    <small v-if="imageMatchForRow(row)">
                                        {{ imageMatchStatusLabel(imageMatchForRow(row).status) }}
                                    </small>
                                </td>
                                <td>
                                    <div class="import-row-actions">
                                        <button
                                            v-if="isRowExcluded(row)"
                                            type="button"
                                            @click="toggleImportRowExclusion(row)"
                                        >
                                            Reincorporar
                                        </button>
                                        <template v-else>
                                            <label
                                                class="row-file-action replace-row-action"
                                                :class="{ disabled: imageReplacementRow !== null }"
                                            >
                                                <input
                                                    type="file"
                                                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                                    :disabled="imageReplacementRow !== null"
                                                    @change="replaceImageForRow(row, $event)"
                                                >
                                                {{ imageReplacementRow === row.sourceRow ? 'Cargando…' : 'Cargar reemplazo' }}
                                            </label>
                                            <button
                                                type="button"
                                                class="generic-row-action"
                                                :disabled="imageReplacementRow !== null"
                                                @click="useGenericImage(row)"
                                            >
                                                Usar genérica
                                            </button>
                                            <button
                                                type="button"
                                                class="exclude-row-action"
                                                :disabled="imageReplacementRow !== null"
                                                @click="toggleImportRowExclusion(row)"
                                            >
                                                Excluir
                                            </button>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <section class="catalog-confirmation">
                    <div class="catalog-readiness-summary">
                        <div>
                            <span>Listas</span>
                            <strong>{{ importReadiness.readyRows }}</strong>
                        </div>
                        <div :class="{ blocked: importReadiness.blockedRows > 0 }">
                            <span>Bloqueadas</span>
                            <strong>{{ importReadiness.blockedRows }}</strong>
                        </div>
                        <div>
                            <span>Con avisos</span>
                            <strong>{{ importReadiness.warningRows }}</strong>
                        </div>
                        <div>
                            <span>Excluidas</span>
                            <strong>{{ importReadiness.excludedRows }}</strong>
                        </div>
                    </div>
                    <div class="catalog-confirm-action">
                        <div>
                            <strong v-if="approvedCatalogModel">
                                Catálogo listo: {{ approvedCatalogModel.products.length }} productos en
                                {{ approvedCatalogModel.categories.length }} categorías.
                            </strong>
                            <span v-else-if="importReadiness.canConfirm">
                                Todos los productos activos están listos para componer.
                            </span>
                            <span v-else>
                                Resolvé las filas bloqueadas y las imágenes pendientes para continuar.
                            </span>
                        </div>
                        <button
                            type="button"
                            :disabled="!importReadiness.canConfirm"
                            @click="confirmImportedCatalog"
                        >
                            {{ approvedCatalogModel ? 'Regenerar catálogo' : 'Generar catálogo' }}
                        </button>
                    </div>
                </section>

                <footer class="import-dialog-footer">
                    <span>Los datos confirmados se convertirán en páginas editables y descargables.</span>
                    <button type="button" class="ghost-button" @click="closeImportPanel">Cerrar revisión</button>
                </footer>
            </template>
        </section>
    </div>
</div>

<script>
    window.CATALOG_ASSET_BASE = <?= json_encode(base_url('assets/img/catalog/'), JSON_UNESCAPED_SLASHES) ?>;
    window.CATALOG_GENERIC_IMAGE = <?= json_encode(base_url('assets/img/catalog/product-placeholder.svg?v=' . filemtime(FCPATH . 'assets/img/catalog/product-placeholder.svg')), JSON_UNESCAPED_SLASHES) ?>;
    window.CATALOG_IMPORT_URL = <?= json_encode(site_url('demo/catalogo/importar'), JSON_UNESCAPED_SLASHES) ?>;
    window.CATALOG_IMAGES_URL = <?= json_encode(site_url('demo/catalogo/imagenes'), JSON_UNESCAPED_SLASHES) ?>;
    window.CATALOG_CSRF = {
        name: <?= json_encode(csrf_token()) ?>,
        hash: <?= json_encode(csrf_hash()) ?>,
    };
</script>
<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<script src="https://unpkg.com/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://unpkg.com/jspdf@4.2.1/dist/jspdf.umd.min.js"></script>
<script src="<?= base_url('assets/js/catalog-presentation.js?v=' . filemtime(FCPATH . 'assets/js/catalog-presentation.js')) ?>"></script>
<script src="<?= base_url('assets/js/catalog-pdf-export.js?v=' . filemtime(FCPATH . 'assets/js/catalog-pdf-export.js')) ?>"></script>
<script src="<?= base_url('assets/js/catalog-import-review.js?v=' . filemtime(FCPATH . 'assets/js/catalog-import-review.js')) ?>"></script>
<script src="<?= base_url('assets/js/catalog-composer.js?v=' . filemtime(FCPATH . 'assets/js/catalog-composer.js')) ?>"></script>
<script src="<?= base_url('assets/js/' . $pageScript . '?v=' . filemtime(FCPATH . 'assets/js/' . $pageScript)) ?>"></script>
</body>
</html>
