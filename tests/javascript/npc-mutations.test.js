import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';
import { test } from 'node:test';
import { JSDOM } from 'jsdom';

const source = fs.readFileSync(new URL('../../src/Public/static/js/npc.js', import.meta.url), 'utf8');

function loadActions() {
    const dom = new JSDOM('<input data-npc-session-string value="first-token"><input name="sstr" value="first-token"><textarea id="questLogText7">Neuer Eintrag</textarea><input id="inviteUserIds7" value="103"><input id="excludeUserIds7" value="101"><div id="questDetails7"></div><input id="reason-101" value="Testeinsicht"><div id="detailsForm-101"></div><div id="playerDetailsInfo-101"></div><div id="playerColonies-101"></div><div id="playerShips-101"></div>');
    dom.window.document.addEventListener = () => {};
    const requests = [];
    const context = vm.createContext({
        document: dom.window.document, window: {}, FormData: globalThis.FormData,
        alert: () => {}, confirm: () => true, console,
        location: { reload: () => {} },
        fetch: async (url, options) => {
            requests.push({ url, options });
            return { text: async () => '<input data-npc-session-string value="renewed-token"><div id="questDetails7">Neue Details</div>' };
        }
    });
    vm.runInContext(source, context);
    return { context, requests, document: dom.window.document };
}

for (const [name, action, args] of [
    ['postQuestLogEntry', 'B_ADD_QUEST_LOG_ENTRY', [7]],
    ['acceptQuestApplication', 'B_ACCEPT_QUEST_APPLICATION', [7, 8]],
    ['rejectQuestApplication', 'B_REJECT_QUEST_APPLICATION', [7, 8]],
    ['inviteQuestUsers', 'B_INVITE_QUEST_USERS', [7]],
    ['excludeQuestUsers', 'B_EXCLUDE_QUEST_USERS', [7]],
    ['endNPCQuest', 'B_END_NPC_QUEST', [7]]
]) {
    test(`${name} sends a protected POST and renews the token`, async () => {
        const { context, requests, document } = loadActions();
        context[name](...args);
        await new Promise(setImmediate);
        assert.equal(requests.length, 1);
        assert.equal(requests[0].url, '/npc/index.php');
        assert.equal(requests[0].options.method, 'POST');
        assert.equal(requests[0].options.body.get(action), '1');
        assert.equal(requests[0].options.body.get('sstr'), 'first-token');
        assert.equal(document.querySelector('[data-npc-session-string]').value, 'renewed-token');
        assert.equal(document.querySelector('[name="sstr"]').value, 'renewed-token');
        await context.sendNpcMutation({ [action]: '1', quest_id: 7 });
        assert.equal(requests[1].options.body.get('sstr'), 'renewed-token');
    });
}

test('player details loads create the audit by protected POST and display the returned data', async () => {
    const { context, requests, document } = loadActions();
    context.fetch = async (url, options) => {
        requests.push({ url, options });
        return { text: async () => '<input data-npc-session-string value="details-token"><div><div>Info</div><div>Kolonien</div><div>Schiffe</div></div>' };
    };
    context.loadPlayerDetails(101);
    await new Promise(setImmediate);
    assert.equal(requests[0].options.body.get('B_LOG_PLAYER_DETAILS'), '1');
    assert.equal(requests[0].options.body.get('SHOW_PLAYER_DETAILS'), '1');
    assert.equal(requests[0].options.body.get('userid'), '101');
    assert.equal(requests[0].options.body.get('reason'), 'Testeinsicht');
    assert.equal(requests[0].options.body.get('sstr'), 'first-token');
    assert.equal(document.getElementById('playerDetailsInfo-101').textContent, 'Info');
    assert.equal(document.getElementById('playerColonies-101').textContent, 'Kolonien');
    assert.equal(document.getElementById('playerShips-101').textContent, 'Schiffe');
    assert.equal(document.querySelector('[data-npc-session-string]').value, 'details-token');
});

test('parallel mutations cannot reuse the same single-use token', async () => {
    const { context, requests } = loadActions();
    const first = context.sendNpcMutation({ B_END_NPC_QUEST: '1', quest_id: 7 });
    await assert.rejects(context.sendNpcMutation({ B_END_NPC_QUEST: '1', quest_id: 7 }));
    await first;
    assert.equal(requests.length, 1);
});

test('NPC actions tolerate the existing repeated script include', () => {
    const { context } = loadActions();
    assert.doesNotThrow(() => vm.runInContext(source, context));
});
