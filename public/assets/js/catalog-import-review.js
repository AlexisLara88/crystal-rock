(function catalogImportReviewModule(root, factory) {
    const api = factory();

    if (typeof module === 'object' && module.exports) {
        module.exports = api;
    } else {
        root.CatalogImportReview = api;
    }
}(typeof globalThis !== 'undefined' ? globalThis : this, () => {
    const normalize = value => String(value ?? '').trim().toLocaleLowerCase('es');

    function parsePrice(value) {
        let numeric = String(value ?? '').replace(/[^\d,.\-]/g, '');
        if (!numeric || numeric === '-') return null;

        const comma = numeric.lastIndexOf(',');
        const dot = numeric.lastIndexOf('.');

        if (comma >= 0 && dot >= 0) {
            const decimal = comma > dot ? ',' : '.';
            const thousands = decimal === ',' ? '.' : ',';
            numeric = numeric.replaceAll(thousands, '').replace(decimal, '.');
        } else if (comma >= 0) {
            const decimals = numeric.length - comma - 1;
            numeric = decimals > 0 && decimals <= 2
                ? numeric.replace(',', '.')
                : numeric.replaceAll(',', '');
        }

        const parsed = Number(numeric);

        return Number.isFinite(parsed) && parsed >= 0
            ? Math.round(parsed * 100) / 100
            : null;
    }

    function formatPrice(value) {
        const parsed = parsePrice(value);
        if (parsed === null) return String(value ?? '').trim();

        return `$${parsed.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })}`;
    }

    function reviewRows({ rows, editsByRow, excludedRows }) {
        const activeRows = rows.filter(row => !excludedRows[String(row.sourceRow)]);
        const codeGroups = new Map();

        activeRows.forEach(row => {
            const edit = editsByRow[String(row.sourceRow)] ?? {};
            const code = normalize(edit.code);

            if (!code) return;
            if (!codeGroups.has(code)) codeGroups.set(code, []);
            codeGroups.get(code).push({
                sourceRow: row.sourceRow,
                category: normalize(edit.category),
            });
        });

        const byRow = {};
        let errorRows = 0;
        let warningRows = 0;
        let excludedCount = 0;

        rows.forEach(row => {
            const key = String(row.sourceRow);
            if (excludedRows[key]) {
                byRow[key] = {
                    status: 'excluded',
                    errors: [],
                    warnings: [],
                };
                excludedCount++;
                return;
            }

            const edit = editsByRow[key] ?? {};
            const errors = [];
            const warnings = [];
            const code = normalize(edit.code);
            const category = normalize(edit.category);

            if (!normalize(edit.name)) errors.push('Falta el nombre del producto.');
            if (!code) errors.push('Falta el código.');
            if (parsePrice(edit.price) === null) errors.push('El precio no tiene un formato válido.');
            if (!category) errors.push('Falta asignar una categoría.');

            const matchingCodes = codeGroups.get(code) ?? [];
            const sameCategory = matchingCodes.filter(candidate => (
                candidate.sourceRow !== row.sourceRow
                && candidate.category !== ''
                && candidate.category === category
            ));
            const otherCategories = matchingCodes.filter(candidate => (
                candidate.sourceRow !== row.sourceRow
                && candidate.category !== ''
                && candidate.category !== category
            ));

            if (sameCategory.length > 0) {
                errors.push('El código se repite dentro de la misma categoría.');
            } else if (otherCategories.length > 0) {
                warnings.push('El código también aparece en otra categoría.');
            }

            const status = errors.length > 0 ? 'error' : (warnings.length > 0 ? 'warning' : 'valid');
            byRow[key] = { status, errors, warnings };
            if (status === 'error') errorRows++;
            if (status === 'warning') warningRows++;
        });

        return {
            byRow,
            summary: {
                totalRows: rows.length,
                activeRows: activeRows.length,
                validRows: activeRows.length - errorRows - warningRows,
                warningRows,
                errorRows,
                excludedRows: excludedCount,
            },
        };
    }

    function buildApprovedModel({
        rows,
        editsByRow,
        excludedRows,
        imageMatchesByRow,
        categories,
    }) {
        const products = rows
            .filter(row => !excludedRows[String(row.sourceRow)])
            .map(row => {
                const key = String(row.sourceRow);
                const edit = editsByRow[key];
                const imageMatch = imageMatchesByRow[key];

                return {
                    sourceRow: row.sourceRow,
                    name: edit.name.trim(),
                    code: edit.code.trim(),
                    price: formatPrice(edit.price),
                    priceNumber: parsePrice(edit.price),
                    category: edit.category.trim(),
                    measurements: row.values.measurements ?? '',
                    material: row.values.material ?? '',
                    packaging: row.values.packaging ?? '',
                    pack: row.values.pack ?? '',
                    master: row.values.master ?? '',
                    imageReference: row.values.image ?? '',
                    imageDecision: imageMatch.status,
                    image: imageMatch.image,
                };
            });

        return {
            version: 1,
            createdAt: new Date().toISOString(),
            categories: categories
                .filter(category => products.some(product => product.category === category))
                .map((name, index) => ({ name, order: index + 1 })),
            products,
        };
    }

    return {
        buildApprovedModel,
        formatPrice,
        parsePrice,
        reviewRows,
    };
}));
