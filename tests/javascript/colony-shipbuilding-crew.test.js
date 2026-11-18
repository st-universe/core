import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';
import { test } from 'node:test';
import { JSDOM } from 'jsdom';

const prototype = fs.readFileSync(new URL('../../src/Public/static/js/prototype.js', import.meta.url), 'utf8');
const source = fs.readFileSync(new URL('../../src/Public/static/js/colony.js', import.meta.url), 'utf8');

function loadShipbuilding() {
    const dom = new JSDOM('<div id="crewdisplay"><div id="crewSum"></div><div id="crewMax"></div><div id="crewerr"></div></div><input id="buildbutton">', { runScripts: 'outside-only' });
    const context = dom.getInternalVMContext();
    vm.runInContext(prototype, context);
    vm.runInContext(source, context);
    return { context, document: dom.window.document };
}

test('NPC gift keeps its saved crew even when modules would exceed the crew limit', () => {
    const { context, document } = loadShipbuilding();
    context.setFixValues(63, 115, 4, 20);
    context.updateCrewCount(1, 70);
    context.updateCrewCount(19403, 10);

    assert.equal(context.checkCrewCount(), true);
    assert.equal(document.getElementById('crewSum').textContent, 'Benötigte Crew: 20');
    assert.equal(document.getElementById('crewerr').style.display, 'none');
});

test('opening a regular buildplan clears the NPC crew override and calculates the modules again', () => {
    const { context, document } = loadShipbuilding();
    context.setFixValues(63, 115, 4, 20);
    context.updateCrewCount(1, 70);
    context.setFixValues(63, 115, 4);
    context.updateCrewCount(1, 5);
    context.updateCrewCount(19403, 10);

    assert.equal(context.checkCrewCount(), true);
    assert.equal(document.getElementById('crewSum').textContent, 'Benötigte Crew: 78');
    context.updateCrewCount(1, 70);
    assert.equal(context.checkCrewCount(), false);
    assert.equal(document.getElementById('buildbutton').disabled, true);
    assert.notEqual(document.getElementById('crewerr').style.display, 'none');
});
