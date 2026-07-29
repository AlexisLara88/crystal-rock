((root, factory) => {
    const api = factory();

    if (typeof module === 'object' && module.exports) {
        module.exports = api;
    }

    if (root) {
        root.CatalogPdfExport = api;
    }
})(typeof globalThis !== 'undefined' ? globalThis : this, () => {
    'use strict';

    const DEFAULT_CAPTURE_OPTIONS = Object.freeze({
        scale: 2,
        useCORS: true,
        allowTaint: false,
        backgroundColor: '#f5ecde',
        logging: false,
        width: 595,
        height: 842,
        windowWidth: 595,
        windowHeight: 842,
    });

    const exportSheets = async ({
        sheets,
        capture,
        PdfConstructor,
        filename = 'crystal-rock-catalogo-actual.pdf',
        onProgress = () => {},
    }) => {
        if (!Array.isArray(sheets) || sheets.length === 0) {
            throw new Error('No hay páginas para exportar.');
        }
        if (typeof capture !== 'function' || typeof PdfConstructor !== 'function') {
            throw new Error('No se pudieron cargar las herramientas de exportación.');
        }

        const pdf = new PdfConstructor({
            orientation: 'portrait',
            unit: 'mm',
            format: 'a4',
            compress: true,
        });

        for (const [index, sheet] of sheets.entries()) {
            await onProgress(index + 1, sheets.length);

            const canvas = await capture(sheet, { ...DEFAULT_CAPTURE_OPTIONS });

            if (!canvas || typeof canvas.toDataURL !== 'function') {
                throw new Error(`No se pudo capturar la página ${index + 1}.`);
            }

            if (index > 0) pdf.addPage('a4', 'portrait');
            pdf.addImage(
                canvas.toDataURL('image/jpeg', 0.94),
                'JPEG',
                0,
                0,
                210,
                297,
                `catalog-page-${index + 1}`,
                'FAST',
            );
        }

        pdf.save(filename);

        return {
            filename,
            pageCount: sheets.length,
        };
    };

    return {
        DEFAULT_CAPTURE_OPTIONS,
        exportSheets,
    };
});
