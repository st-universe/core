import assert from "node:assert/strict";
import fs from "node:fs";
import vm from "node:vm";
import { test } from "node:test";
import { JSDOM } from "jsdom";

const source = fs.readFileSync(new URL("../../src/Public/static/js/default.js", import.meta.url), "utf8");

function page(content) {
    const dom = new JSDOM(`<div id="box"><div id="message" class="translatable-content">${content}</div></div>`);
    const context = vm.createContext({ window: dom.window, document: dom.window.document });
    vm.runInContext(source, context);
    context.initTranslations();
    return { context, document: dom.window.document, window: dom.window };
}

for (const text of ['" onmouseover="window.compromised=1', '&lt;img src=x onerror="window.compromised=1"&gt;', '&amp;lt;img src=x onerror=window.compromised=1&amp;gt;']) {
    test(`translation metadata stays inert for ${text}`, () => {
        const { context, document, window } = page(`[translate]${text}[translation]${text} translated[/translate]`);
        const section = document.querySelector('.translatable');
        assert.equal(section.attributes.length, 3);
        for (const toggle of [() => section.dispatchEvent(new window.MouseEvent('click', { bubbles: true })),
            () => context.toggleAll(document.getElementById('box')), () => context.toggleTranslation('message')]) {
            toggle();
            assert.equal(document.querySelectorAll('[onmouseover], [onerror], img, script').length, 0);
            assert.equal(window.compromised, undefined);
        }
        context.initTranslations();
        assert.equal(document.querySelectorAll('.translatable').length, 1);
        assert.equal(document.querySelectorAll('[onmouseover], [onerror], img, script').length, 0);
    });
}

test('ordinary translations preserve generated BBCode formatting and all switchers', () => {
    const { context, document, window } = page('[translate]<strong>Original &amp; Text</strong>[translation]<em>Translation &amp; Text</em>[/translate]');
    const section = document.querySelector('.translatable');
    assert.equal(section.querySelector('strong').textContent, 'Original & Text');
    section.querySelector('strong').dispatchEvent(new window.MouseEvent('click', { bubbles: true }));
    section.dispatchEvent(new window.MouseEvent('click', { bubbles: true }));
    assert.equal(section.querySelector('em').textContent, 'Translation & Text');
    context.toggleAll(document.getElementById('box'));
    assert.equal(section.querySelector('strong').textContent, 'Original & Text');
    context.toggleTranslation('message');
    assert.equal(section.querySelector('em').textContent, 'Translation & Text');
});
