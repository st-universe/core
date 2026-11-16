import assert from 'node:assert/strict';
import fs from 'node:fs';
import { test } from 'node:test';
import { JSDOM } from 'jsdom';

const source = fs.readFileSync(new URL('../../src/Public/static/js/spacecraft.js', import.meta.url), 'utf8');

function createInterface(active = true, initial = 0, elapsedSeconds = 0) {
    const dom = new JSDOM(`<div data-assimilation-active="${active ? 1 : 0}" data-assimilation-progress="${initial / 100}"
        data-assimilation-start="1000" data-assimilation-now="${1000 + elapsedSeconds}" data-assimilation-initial="${initial}" data-assimilation-duration="900">
        <img class="normal" /><img class="borg-assimilation-overlay" />
        <span data-assimilation-value></span><span data-assimilation-countdown-label></span><span data-assimilation-remaining></span>
    </div>`, { runScripts: 'outside-only' });
    let now = 1000000;
    let tick;
    dom.window.Date.now = () => now;
    dom.window.setInterval = callback => { tick = callback; };
    dom.window.eval(source);
    dom.window.document.dispatchEvent(new dom.window.Event('DOMContentLoaded'));
    return {
        document: dom.window.document,
        advance(milliseconds) { now += milliseconds; tick(); },
        close() { dom.window.close(); }
    };
}

test('progress advances each full second and reveals the assimilated image from the right', () => {
    const ui = createInterface();
    try {
        ui.advance(999);
        assert.equal(ui.document.querySelector('[data-assimilation-value]').textContent, '0,0%');
        ui.advance(1);
        assert.equal(ui.document.querySelector('[data-assimilation-value]').textContent, '0,1%');
        assert.equal(ui.document.querySelector('.borg-assimilation-overlay').style.clipPath, 'inset(0 0 0 99.89%)');
        ui.advance(1000);
        assert.equal(ui.document.querySelector('[data-assimilation-value]').textContent, '0,2%');
        assert.equal(ui.document.querySelector('.borg-assimilation-overlay').style.clipPath, 'inset(0 0 0 99.78%)');
        ui.advance(58000);
        assert.equal(ui.document.querySelector('[data-assimilation-value]').textContent, '6,7%');
        assert.equal(ui.document.querySelector('.borg-assimilation-overlay').style.clipPath, 'inset(0 0 0 93.34%)');
        assert.equal(ui.document.querySelector('[data-assimilation-remaining]').textContent, '14m 0s');
        ui.advance(840000);
        assert.equal(ui.document.querySelector('[data-assimilation-value]').textContent, '100,0%');
        assert.equal(ui.document.querySelector('.normal').style.visibility, 'hidden');
        assert.equal(ui.document.querySelector('.borg-assimilation-overlay').style.clipPath, 'inset(0 0 0 0%)');
    } finally { ui.close(); }
});

test('opening the page between minute ticks immediately shows the elapsed seconds', () => {
    const ui = createInterface(true, 0, 37);
    try {
        assert.equal(ui.document.querySelector('[data-assimilation-value]').textContent, '4,1%');
        assert.equal(ui.document.querySelector('.borg-assimilation-overlay').style.clipPath, 'inset(0 0 0 95.89%)');
        assert.equal(ui.document.querySelector('[data-assimilation-remaining]').textContent, '14m 23s');
        ui.advance(1000);
        assert.equal(ui.document.querySelector('[data-assimilation-value]').textContent, '4,2%');
    } finally { ui.close(); }
});

test('freed ship images retain their frozen partial progress', () => {
    const ui = createInterface(false, 2500);
    try {
        ui.advance(900000);
        assert.equal(ui.document.querySelector('[data-assimilation-progress]').dataset.assimilationProgress, '25');
    } finally { ui.close(); }
});

test('a recaptured ship continues from its existing partial assimilation across a full fifteen minute timer', () => {
    const ui = createInterface(true, 5000);
    try {
        ui.advance(450000);
        assert.equal(ui.document.querySelector('[data-assimilation-value]').textContent, '75,0%');
        assert.equal(ui.document.querySelector('[data-assimilation-remaining]').textContent, '7m 30s');
        ui.advance(450000);
        assert.equal(ui.document.querySelector('[data-assimilation-value]').textContent, '100,0%');
    } finally { ui.close(); }
});

test('the exact deadline shows processing while the minute tick has not confirmed the transfer', () => {
    const ui = createInterface();
    try {
        ui.advance(899000);
        assert.equal(ui.document.querySelector('[data-assimilation-value]').textContent, '99,9%');
        assert.equal(ui.document.querySelector('[data-assimilation-remaining]').textContent, '0m 1s');
        assert.equal(ui.document.querySelector('[data-assimilation-countdown-label]').textContent, 'Verbleibende Zeit: ');
        ui.advance(1000);
        assert.equal(ui.document.querySelector('[data-assimilation-value]').textContent, '100,0%');
        assert.equal(ui.document.querySelector('[data-assimilation-remaining]').textContent, 'Abschluss wird verarbeitet');
        assert.equal(ui.document.querySelector('[data-assimilation-countdown-label]').textContent, '');
        ui.advance(30000);
        assert.equal(ui.document.querySelector('[data-assimilation-remaining]').textContent, 'Abschluss wird verarbeitet');
    } finally { ui.close(); }
});
