<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Muestra visual del generador automático de catálogos Crystal Rock">
    <title>Crystal Rock · Estudio de catálogo</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/catalog-demo.css') ?>">
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
            Demo visual · D1
        </div>

        <div class="header-actions">
            <label class="theme-select">
                <span>Estilo de portada</span>
                <select v-model="coverVariant">
                    <option value="editorial">Editorial</option>
                    <option value="promotional">Campaña</option>
                    <option value="minimal">Minimal claro</option>
                </select>
            </label>
            <button type="button" class="ghost-button" @click="showAll = !showAll">
                {{ showAll ? 'Ver una página' : 'Ver catálogo completo' }}
            </button>
        </div>
    </header>

    <main class="studio-main">
        <aside class="studio-sidebar">
            <div class="sidebar-intro">
                <span class="eyebrow">Sistema visual</span>
                <h1>Una identidad, siete composiciones.</h1>
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
                </div>

                <div class="preview-controls">
                    <button type="button" @click="previousPage" :disabled="showAll || currentPage === 0" aria-label="Página anterior">←</button>
                    <span>{{ currentPage + 1 }} / {{ pages.length }}</span>
                    <button type="button" @click="nextPage" :disabled="showAll || currentPage === pages.length - 1" aria-label="Página siguiente">→</button>
                </div>
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
                            <div class="cover-promo">
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
                            >ACTUALIZADO: 28/07/26</div>
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
                                >Cristalería</h2>
                                <p
                                    :class="textSelectionClass(coverTemplateId(), 'coverSubtitle')"
                                    :style="textStyle(coverTemplateId(), 'coverSubtitle')"
                                    @pointerdown="selectText(coverTemplateId(), 'coverSubtitle', $event)"
                                >Calidad real para casas reales</p>
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
                                    >CRISTALERÍA</div>
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
                                    >Copas Gin<br>Tonic 590 ML</div>
                                    <div class="featured-cutout-slot">
                                        <div
                                            :class="['featured-cutout', 'image-mask', { active: activeImageKey === 'featured-' + featuredProduct.code }]"
                                            :style="maskStyle('featured-' + featuredProduct.code)"
                                            @pointerdown="startImageDrag('featured-' + featuredProduct.code, 'Copas Gin Tonic 590 ML', $event)"
                                            @pointermove="dragImage"
                                            @pointerup="endImageDrag"
                                            @pointercancel="endImageDrag"
                                        >
                                            <img
                                                :src="asset('glass-gin.png')"
                                                alt="Copa Gin Tonic"
                                                :style="imageStyle('featured-' + featuredProduct.code)"
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
                                        <template v-for="spec in featuredProduct.specs" :key="spec[0]">
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
                                        ><b>Cod.</b> {{ featuredProduct.code }}</div>
                                        <div
                                            class="featured-price"
                                            :class="textSelectionClass('featured', 'productPrice')"
                                            :style="textStyle('featured', 'productPrice')"
                                            @pointerdown="selectText('featured', 'productPrice', $event)"
                                        >Ud. {{ featuredProduct.price }}</div>
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
                                    v-for="product in products.slice(0, page.count)"
                                    :key="page.id + product.code"
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
                                            :class="['product-image', 'image-mask', { active: activeImageKey === product.code }]"
                                            :style="maskStyle(product.code)"
                                            @pointerdown="startImageDrag(product.code, product.name, $event)"
                                            @pointermove="dragImage"
                                            @pointerup="endImageDrag"
                                            @pointercancel="endImageDrag"
                                        >
                                            <img
                                                :src="asset(product.image)"
                                                :alt="product.name"
                                                :style="imageStyle(product.code)"
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
        <span class="image-editor-kicker">Ajuste de imagen</span>
        <strong>{{ activeImageAdjustment.label }}</strong>
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
        <span class="image-editor-kicker">Elemento de texto</span>
        <strong>{{ activeTextInspector.label }}</strong>

        <dl class="text-selection-meta">
            <dt>Plantilla</dt>
            <dd>{{ activeTextInspector.templateLabel }}</dd>
            <dt>Alcance</dt>
            <dd>Todas las instancias de este rol</dd>
        </dl>

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
                    <button type="button" @click="nudgeSelectedText(0, -4)" aria-label="Mover arriba">↑</button>
                    <button type="button" @click="nudgeSelectedText(-4, 0)" aria-label="Mover a la izquierda">←</button>
                    <button type="button" class="position-center" disabled aria-label="Posición actual">●</button>
                    <button type="button" @click="nudgeSelectedText(4, 0)" aria-label="Mover a la derecha">→</button>
                    <button type="button" @click="nudgeSelectedText(0, 4)" aria-label="Mover abajo">↓</button>
                </div>
                <small>También podés arrastrar directamente el texto seleccionado.</small>
            </div>

            <label class="inspector-field dimension-field">
                <span>Ancho del bloque</span>
                <input
                    type="range"
                    min="60"
                    max="100"
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
                    min="60"
                    max="100"
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

        <button type="button" class="text-reset-button" @click="resetSelectedText">
            Restablecer elemento
        </button>
        <small>La selección y este panel no aparecen en el PDF.</small>
    </aside>
</div>

<script>
    window.CATALOG_ASSET_BASE = <?= json_encode(base_url('assets/img/catalog/'), JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<script src="<?= base_url('assets/js/catalog-presentation.js') ?>"></script>
<script src="<?= base_url('assets/js/' . $pageScript) ?>"></script>
</body>
</html>
