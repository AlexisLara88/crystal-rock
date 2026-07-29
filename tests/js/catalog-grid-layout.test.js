'use strict';

const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const assert = require('node:assert/strict');

const stylesheet = fs.readFileSync(
    path.join(__dirname, '../../public/assets/css/catalog-demo.css'),
    'utf8',
);

test('la grilla individual replica la retícula vertical del producto destacado', () => {
    assert.match(
        stylesheet,
        /\.product-grid-1\s*\{[^}]*grid-template-rows:\s*680px;[^}]*align-content:\s*center;/s,
    );
    assert.match(
        stylesheet,
        /\.product-grid-1 \.product-card\s*\{[^}]*grid-template-columns:\s*62% 38%;[^}]*grid-template-rows:\s*55% 22% 23%;/s,
    );
    assert.match(
        stylesheet,
        /\.product-grid-1 \.product-image-slot\s*\{[^}]*grid-row:\s*1;[^}]*grid-column:\s*1 \/ -1;/s,
    );
    assert.match(
        stylesheet,
        /\.product-grid-1 \.product-title\s*\{[^}]*grid-row:\s*2;[^}]*grid-column:\s*1 \/ -1;/s,
    );
    assert.match(
        stylesheet,
        /\.product-grid-1 \.product-data\s*\{[^}]*grid-row:\s*3;[^}]*grid-column:\s*1 \/ -1;/s,
    );
});
