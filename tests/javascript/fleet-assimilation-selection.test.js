import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';
import { test } from 'node:test';

const source = fs.readFileSync(new URL('../../src/Public/static/js/ship.js', import.meta.url), 'utf8');

function createSelection(checkboxes, tablePresent = true) {
    const requests = [];
    let closed = 0;
    const context = vm.createContext({
        $: id => id === 'availableShipsTable' ? (tablePresent ? {} : null) : { value: 'token' },
        Element: { select: () => ({ each: callback => checkboxes.forEach(callback) }) },
        Ajax: { Updater: function (target, url, options) { requests.push({ target, url, options }); } },
        closeAjaxWindow() { closed++; }
    });
    vm.runInContext(source, context);
    return { join: () => context.joinFleetInShiplist(10), requests, closed: () => closed };
}

test('joining a fleet does not submit empty selections or close an empty popup', () => {
    const ui = createSelection([]);
    ui.join();
    assert.equal(ui.requests.length, 0);
    assert.equal(ui.closed(), 0);
});

test('checked disabled ships are excluded from fleet joins', () => {
    const ui = createSelection([{ value: '77', checked: true, disabled: true }]);
    ui.join();
    assert.equal(ui.requests.length, 0);
    assert.equal(ui.closed(), 0);
});

test('only checked enabled ship ids are posted and the popup closes after submission', () => {
    const ui = createSelection([
        { value: '77', checked: true, disabled: true },
        { value: '78', checked: true, disabled: false },
        { value: '79', checked: false, disabled: false }
    ]);
    ui.join();
    assert.equal(ui.requests.length, 1);
    assert.deepEqual(Array.from(ui.requests[0].options.parameters['chosen[]']), ['78']);
    assert.equal(ui.requests[0].options.parameters.sstr, 'token');
    assert.equal(ui.closed(), 1);
});

test('a stale join callback does nothing after its selection table disappeared', () => {
    const ui = createSelection([], false);
    ui.join();
    assert.equal(ui.requests.length, 0);
});
