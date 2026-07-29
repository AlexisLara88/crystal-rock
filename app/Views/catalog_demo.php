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

            <section class="image-editor" aria-live="polite">
                <template v-if="activeImageAdjustment">
                    <span class="image-editor-kicker">Ajuste de imagen</span>
                    <strong>{{ activeImageAdjustment.label }}</strong>
                    <p>Arrastrá la imagen directamente dentro de la máscara o usá los controles.</p>

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

                    <div class="position-controls" aria-label="Posición de la imagen">
                        <button type="button" @click="nudgeImage(0, -8)" aria-label="Mover arriba">↑</button>
                        <button type="button" @click="nudgeImage(-8, 0)" aria-label="Mover a la izquierda">←</button>
                        <button type="button" @click="resetImageAdjustment" aria-label="Centrar imagen">●</button>
                        <button type="button" @click="nudgeImage(8, 0)" aria-label="Mover a la derecha">→</button>
                        <button type="button" @click="nudgeImage(0, 8)" aria-label="Mover abajo">↓</button>
                    </div>

                    <button type="button" class="reset-image" @click="resetImageAdjustment">
                        Centrar y restablecer
                    </button>
                </template>
                <template v-else>
                    <span class="image-editor-kicker">Ajuste de imagen</span>
                    <strong>Seleccioná una imagen</strong>
                    <p>Hacé clic sobre una imagen de producto para moverla o cambiar su zoom.</p>
                </template>
            </section>

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
                                <small v-if="coverVariant === 'promotional'">¡Nuevo!</small>
                                <span>Hacé tu compra por<br>la <b>web</b> y obtené un</span>
                                <strong>10% OFF</strong>
                            </div>
                            <div class="date-pill">ACTUALIZADO: 28/07/26</div>
                            <div class="cover-line line-one"></div>
                            <div class="cover-line line-two"></div>
                            <div class="cover-title">
                                <span class="catalog-wordmark">CRYSTALROCK</span>
                                <h2>Cristalería</h2>
                                <p>Calidad real para casas reales</p>
                            </div>
                        </template>

                        <template v-else-if="page.type === 'featured'">
                            <div class="featured-shell">
                                <div class="featured-hero">
                                    <img :src="asset('feature-wine.jpg')" alt="">
                                    <div class="category-ribbon">CRISTALERÍA</div>
                                    <div class="featured-label">Producto<br>destacado</div>
                                </div>
                                <div class="featured-product">
                                    <div class="featured-name">Copas Gin<br>Tonic 590 ML</div>
                                    <div
                                        :class="['featured-cutout', 'image-mask', { active: activeImageKey === 'featured-' + featuredProduct.code }]"
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
                                    <dl class="product-specs featured-specs">
                                        <template v-for="spec in featuredProduct.specs" :key="spec[0]">
                                            <dt>{{ spec[0] }}</dt>
                                            <dd>{{ spec[1] }}</dd>
                                        </template>
                                    </dl>
                                    <div class="featured-commerce">
                                        <div class="featured-code"><b>Cod.</b> {{ featuredProduct.code }}</div>
                                        <div class="featured-price">Ud. {{ featuredProduct.price }}</div>
                                    </div>
                                </div>
                            </div>
                            <footer class="sheet-footer light-footer">
                                <span class="catalog-wordmark footer-wordmark">CRYSTALROCK</span>
                                <i></i>
                                <span>PARA CASAS REALES</span>
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
                                        <div class="product-title">{{ product.name }}</div>
                                        <div class="product-data">
                                            <dl class="product-specs">
                                                <template v-for="spec in product.specs" :key="spec[0]">
                                                    <dt>{{ spec[0] }}</dt>
                                                    <dd>{{ spec[1] }}</dd>
                                                </template>
                                            </dl>
                                            <div class="product-commerce">
                                                <div class="product-code"><b>Cod.</b> {{ product.code }}</div>
                                                <div class="product-price">Ud. {{ product.price }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        :class="['product-image', 'image-mask', { active: activeImageKey === product.code }]"
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
                                </section>
                            </div>
                            <footer class="sheet-footer light-footer grid-footer">
                                <span class="catalog-wordmark footer-wordmark">CRYSTALROCK</span>
                                <i></i>
                                <span>PARA CASAS REALES</span>
                            </footer>
                        </template>

                        <template v-else-if="page.type === 'back'">
                            <img class="sheet-background" :src="asset('back-community.png')" alt="">
                            <div class="back-shade"></div>
                            <div class="back-content">
                                <span class="catalog-wordmark back-wordmark">CRYSTALROCK</span>
                                <div class="order-card">
                                    <h2>¿Cómo hacer tu pedido?</h2>
                                    <hr>
                                    <strong>Por nuestra web</strong>
                                    <a href="https://www.crystalrock.com.ar">www.crystalrock.com.ar <span>↗</span></a>
                                    <p>¡Obtenés un 10% OFF!</p>
                                    <strong>Por WhatsApp</strong>
                                    <a href="#">Atención personalizada <span>↗</span></a>
                                </div>
                                <div class="community-cta">
                                    <h3>¡Sumate a nuestra<br>comunidad en las redes!</h3>
                                    <a href="#">Instagram</a>
                                </div>
                            </div>
                        </template>
                    </div>
                    <p class="page-caption">{{ showAll ? index + 1 : currentPage + 1 }} · {{ page.label }}</p>
                </article>
            </div>
        </section>
    </main>
</div>

<script>
    window.CATALOG_ASSET_BASE = <?= json_encode(base_url('assets/img/catalog/'), JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<script src="<?= base_url('assets/js/' . $pageScript) ?>"></script>
</body>
</html>
