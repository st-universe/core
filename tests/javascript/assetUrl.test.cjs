const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const path = require('node:path');
const context = vm.createContext({ window: { STU_ASSET_VERSIONS: { 'buttons/test.png': '1234567890abcdef1234' } } });
vm.runInContext(fs.readFileSync(path.join(__dirname, '../../src/Public/static/js/assetUrl.js'), 'utf8'), context);

test('versioned URLs preserve parameters and fragments and replace old versions', () => {
    assert.equal(context.assetUrl('/assets/buttons/test.png?size=2&v=old#icon'), '/assets/buttons/test.png?size=2&v=1234567890abcdef1234#icon');
    assert.equal(context.assetUrl('assets/buttons/test.png'), 'assets/buttons/test.png?v=1234567890abcdef1234');
    const url = context.assetUrl('/assets/buttons/test.png');
    assert.equal(context.assetUrl(url), url);
});

test('external and unknown URLs are untouched', () => {
    for (const url of ['https://example.org/assets/buttons/test.png', '//example.org/assets/buttons/test.png', '/assets/missing.png', '/other.png']) {
        assert.equal(context.assetUrl(url), url);
    }
});

test('missing manifest falls back to original URLs', () => {
    delete context.window.STU_ASSET_VERSIONS;
    assert.equal(context.assetUrl('/assets/buttons/test.png'), '/assets/buttons/test.png');
});

test('new scripts remain usable on a page opened before the deployment', () => {
    const legacy = vm.createContext({ document: { images: { button: {} } }, gfx_path: '/assets' });
    legacy.window = legacy;
    const defaultScript = fs.readFileSync(path.join(__dirname, '../../src/Public/static/js/default.js'), 'utf8');
    vm.runInContext(defaultScript.split('\n')[0], legacy);
    vm.runInContext(defaultScript.match(/function cp\([^]*?\n\}/)[0], legacy);
    legacy.cp('button', 'buttons/test');
    assert.equal(legacy.document.images.button.src, '/assets/buttons/test.png');

    legacy.STU_ASSET_VERSIONS = { 'buttons/test.png': '1234567890abcdef1234' };
    vm.runInContext(fs.readFileSync(path.join(__dirname, '../../src/Public/static/js/assetUrl.js'), 'utf8'), legacy);
    const resolver = legacy.assetUrl;
    for (const name of ['default', 'colony', 'npc', 'spacecraft', 'main', 'adminLiveMap', 'database', 'mapEditor']) {
        const script = fs.readFileSync(path.join(__dirname, '../../src/Public/static/js/' + name + '.js'), 'utf8');
        vm.runInContext(script.split('\n')[0], legacy);
        assert.equal(legacy.assetUrl, resolver);
    }
    legacy.cp('button', 'buttons/test');
    assert.equal(legacy.document.images.button.src, '/assets/buttons/test.png?v=1234567890abcdef1234');
});
