import assert from 'node:assert/strict';
import fs from 'node:fs';
import { test } from 'node:test';
import { JSDOM } from 'jsdom';

const source = fs.readFileSync(new URL('../../src/Public/static/js/borgAssimilation.js', import.meta.url), 'utf8');
const storageKey = 'stu:anniversary:25:borg';

async function createInterface({ elapsed = 0, user = null, stored, blockedStorage = false, reduced = false, epoch = 0, theme = '', preparing = false, deferSkins = false, nativeAnimations = false, enabled = true, restarted = false, legacyPaused = false, markup = '<div class="navigation_item">Maindesk</div>' } = {}) {
    const dom = new JSDOM(`<html><head><style>${theme}</style><script id="borg-assimilation-config" type="application/json">${JSON.stringify({ elapsed, epoch, duration: 150000, user, enabled, restarted, surface: user === null ? 'public' : 'game' })}</script></head><body>${markup}</body></html>`, {
        url: 'https://stu.example/', runScripts: 'outside-only', pretendToBeVisual: true
    });
    const { window } = dom;
    let now = 1000000;
    let nextId = 1;
    const timers = new Map();
    const frames = new Map();
    let rectangleReads = 0;
    const decoders = [];
    const animations = [];
    if (nativeAnimations) window.HTMLElement.prototype.animate = function (frames) {
        const animation = { currentTime: 0, playState: 'running', play() { this.playState = 'running'; }, pause() { this.playState = 'paused'; }, cancel() { this.playState = 'idle'; } };
        animations.push({ element: this, frames });
        return animation;
    };
    if (preparing) window.document.documentElement.setAttribute('data-borg-preparing', '');
    if (deferSkins) window.Image.prototype.decode = () => new Promise(resolve => decoders.push(resolve));
    window.Date.now = () => now;
    const motionListeners = [];
    const motionPreference = { matches: reduced, addEventListener: (_, listener) => motionListeners.push(listener) };
    window.matchMedia = () => motionPreference;
    const setReducedMotion = matches => { motionPreference.matches = matches; motionListeners.forEach(listener => listener()); };
    window.requestAnimationFrame = callback => { const id = nextId++; frames.set(id, callback); return id; };
    window.cancelAnimationFrame = id => frames.delete(id);
    window.setTimeout = callback => { const id = nextId++; timers.set(id, callback); return id; };
    window.clearTimeout = id => timers.delete(id);
    Object.defineProperty(window.HTMLElement.prototype, 'offsetWidth', { get() { return Number(this.dataset.width || 200); } });
    Object.defineProperty(window.HTMLElement.prototype, 'offsetHeight', { get() { return Number(this.dataset.height || 80); } });
    Object.defineProperty(window.HTMLElement.prototype, 'offsetLeft', { get() { return Number(this.dataset.x || 0); } });
    Object.defineProperty(window.HTMLElement.prototype, 'offsetTop', { get() { return Number(this.dataset.y || 0); } });
    Object.defineProperty(window.HTMLElement.prototype, 'offsetParent', { get() { return this.parentElement?.closest('[data-layout]') || null; } });
    window.HTMLElement.prototype.getBoundingClientRect = () => { rectangleReads++; throw new Error('Viewport measurements must not be needed'); };
    if (stored) window.sessionStorage.setItem(storageKey, JSON.stringify(stored));
    if (legacyPaused) window.sessionStorage.setItem(`${storageKey}:paused`, 'true');
    if (blockedStorage) Object.defineProperty(window, 'sessionStorage', { get() { throw new Error('Storage blocked'); } });
    window.eval(source);
    await new Promise(resolve => setTimeout(resolve, 0));

    function advance(milliseconds) {
        now += milliseconds;
        const callbacks = [...timers.values(), ...frames.values()];
        timers.clear();
        frames.clear();
        for (const callback of callbacks) callback(now);
    }

    return { window, document: window.document, animations, advance, setReducedMotion, releaseSkins: () => decoders.splice(0).forEach(resolve => resolve()), get rectangleReads() { return rectangleReads; },
        get scheduledWork() { return timers.size + frames.size; }, close: () => dom.window.close() };
}

test('the original interface starts dormant and assimilates to a permanent final state', async () => {
    const ui = await createInterface();
    try {
        assert.equal(ui.document.documentElement.dataset.borgAssimilation, 'dormant');
        assert.equal(ui.window.stuBorgAssimilation.getProgress(), 0);
        ui.advance(75000);
        assert.equal(ui.document.documentElement.dataset.borgAssimilation, 'active');
        assert.equal(ui.window.stuBorgAssimilation.getProgress(), 0.5);
        assert.match(ui.document.querySelector('.borg-console').style.getPropertyValue('--borg-skin'), /data:image\/svg/);
        ui.advance(75000);
        assert.equal(ui.document.documentElement.dataset.borgAssimilation, 'complete');
        ui.advance(600000);
        assert.equal(ui.window.stuBorgAssimilation.getProgress(), 1);
    } finally { ui.close(); }
});

test('a new document restores fractional progress before its first paint', async () => {
    const ui = await createInterface({ elapsed: 30000, stored: { owner: 'guest', elapsed: 30520, savedAt: 999800 } });
    try {
        assert.equal(ui.window.stuBorgAssimilation.getProgress(), 30720 / 150000);
        assert.equal(ui.document.documentElement.dataset.borgAssimilation, 'active');
    } finally { ui.close(); }
});

test('logging in adopts the guest timeline', async () => {
    const ui = await createInterface({ user: 101, elapsed: 30000, stored: { owner: 'guest', elapsed: 30750, savedAt: 1000000 } });
    try {
        assert.equal(ui.window.stuBorgAssimilation.getProgress(), 30750 / 150000);
        assert.equal(JSON.parse(ui.window.sessionStorage.getItem(storageKey)).owner, '101');
    } finally { ui.close(); }
});

test('switching accounts does not inherit another account timeline', async () => {
    const ui = await createInterface({ user: 102, elapsed: 5000, stored: { owner: '101', elapsed: 150000, savedAt: 1000000 } });
    try {
        assert.equal(ui.window.stuBorgAssimilation.getProgress(), 5000 / 150000);
    } finally { ui.close(); }
});

test('account progress from the server wins over an older browser state', async () => {
    const ui = await createInterface({ user: 101, elapsed: 150000, stored: { owner: '101', elapsed: 10000, savedAt: 1000000 } });
    try {
        assert.equal(ui.window.stuBorgAssimilation.getProgress(), 1);
        assert.equal(ui.document.documentElement.dataset.borgAssimilation, 'complete');
    } finally { ui.close(); }
});

test('adjusting the device clock backwards never reverses assimilation', async () => {
    const ui = await createInterface({ elapsed: 75000 });
    try {
        ui.advance(10000);
        const reached = ui.window.stuBorgAssimilation.getProgress();
        ui.advance(-20000);
        assert.equal(ui.window.stuBorgAssimilation.getProgress(), reached);
    } finally { ui.close(); }
});

test('denied browser storage does not stop the effect or create duplicate layers', async () => {
    const ui = await createInterface({ elapsed: 50000, blockedStorage: true });
    try {
        ui.window.eval(source);
        assert.equal(ui.document.querySelectorAll('.borg-circuitry').length, 0);
        assert.equal(ui.document.querySelectorAll('.borg-uplink').length, 0);
        assert.equal(ui.document.querySelectorAll('.borg-backdrop').length, 1);
        ui.advance(1000);
        assert.ok(ui.window.stuBorgAssimilation.getProgress() > 0.3);
    } finally { ui.close(); }
});

test('reduced motion removes animated energy packets while retaining assimilation', async () => {
    const ui = await createInterface({ elapsed: 75000, reduced: true });
    try {
        assert.equal(ui.document.documentElement.dataset.borgMotion, 'still');
        ui.advance(1000);
        assert.match(ui.document.querySelector('.borg-console').style.getPropertyValue('--borg-skin'), /data:image\/svg/);
    } finally { ui.close(); }
});

test('the system motion preference can change without resetting progress', async () => {
    const ui = await createInterface({ elapsed: 75000 });
    try {
        ui.setReducedMotion(true);
        assert.equal(ui.document.documentElement.dataset.borgMotion, 'still');
        assert.equal(ui.window.stuBorgAssimilation.getProgress(), .5);
        ui.setReducedMotion(false);
        assert.equal(ui.document.documentElement.dataset.borgMotion, 'animated');
    } finally { ui.close(); }
});

test('AJAX consoles join the current stage without restarting the timeline', async () => {
    const ui = await createInterface({ elapsed: 150000 });
    try {
        ui.advance(0);
        const before = ui.document.querySelectorAll('.borg-console').length;
        const panel = ui.document.createElement('div');
        panel.className = 'box';
        panel.textContent = 'Nachgeladene Konsole';
        ui.document.body.appendChild(panel);
        await Promise.resolve();
        ui.advance(1000);
        assert.equal(ui.document.querySelectorAll('.borg-console').length, before + 1);
        assert.match(panel.style.getPropertyValue('--borg-skin'), /data:image\/svg/);
        assert.equal(ui.window.stuBorgAssimilation.getProgress(), 1);
    } finally { ui.close(); }
});

test('completed assimilation stops scheduling JavaScript redraws and never reads viewport positions', async () => {
    const ui = await createInterface({ elapsed: 150000 });
    try {
        assert.equal(ui.scheduledWork, 0);
        ui.window.dispatchEvent(new ui.window.Event('scroll'));
        ui.document.querySelector('.navigation_item').style.transform = 'translate(100px, 200px)';
        await Promise.resolve();
        assert.equal(ui.rectangleReads, 0);
        assert.equal(ui.scheduledWork, 0);
        assert.equal(ui.document.querySelector('canvas'), null);
    } finally { ui.close(); }
});

test('consoles use different structural patterns and colour families', async () => {
    const ui = await createInterface({ elapsed: 150000 });
    try {
        for (let index = 0; index < 30; index++) {
            const panel = ui.document.createElement('div');
            panel.className = 'box';
            panel.id = `console-${index}`;
            ui.document.body.appendChild(panel);
        }
        await Promise.resolve();
        ui.advance(1);
        const panels = [...ui.document.querySelectorAll('.borg-console')];
        assert.equal(new Set(panels.map(panel => panel.dataset.borgVariant)).size, 6);
        assert.ok(new Set(panels.map(panel => panel.style.getPropertyValue('--borg-skin'))).size > 6);
    } finally { ui.close(); }
});

test('a newer server timeline discards stale progress after a restart', async () => {
    const ui = await createInterface({ user: 101, epoch: 2000, stored: { owner: '101', epoch: 1000, elapsed: 150000, savedAt: 1000000 } });
    try {
        assert.equal(ui.window.stuBorgAssimilation.getProgress(), 0);
    } finally { ui.close(); }
});


test('an active colony grows natively without replacing its SVG on each timer tick', async () => {
    const ui = await createInterface();
    try {
        const panel = ui.document.querySelector('.borg-console');
        for (let index = 0; index < 100 && !decodeURIComponent(panel.style.getPropertyValue('--borg-skin')).includes('attributeName="r"'); index++) ui.advance(500);
        const skin = panel.style.getPropertyValue('--borg-skin');
        const svg = decodeURIComponent(skin);
        assert.match(svg, /attributeName="r"/);
        assert.match(svg, /id="colony"/);
        assert.match(svg, /keyTimes="0;.17;.42;.73;1"/);
        assert.doesNotMatch(svg, /<linearGradient/);
        assert.match(svg, /mask="url\(#growth\)"/);
        const before = ui.window.stuBorgAssimilation.getProgress();
        for (let index = 0; index < 12; index++) ui.advance(50);
        assert.ok(ui.window.stuBorgAssimilation.getProgress() > before);
        assert.equal(panel.style.getPropertyValue('--borg-skin'), skin);
        assert.equal(ui.rectangleReads, 0);
    } finally { ui.close(); }
});

test('reduced motion never embeds moving fronts or energy loops', async () => {
    const ui = await createInterface({ reduced: true });
    try {
        for (let index = 0; index < 15; index++) {
            ui.advance(10000);
            const svg = decodeURIComponent(ui.document.querySelector('.borg-console').style.getPropertyValue('--borg-skin'));
            assert.doesNotMatch(svg, /<animate/);
        }
    } finally { ui.close(); }
});


test('a restored document resumes the native front at the saved fractional position', async () => {
    const ui = await createInterface();
    let restored;
    try {
        const panel = ui.document.querySelector('.borg-console');
        const readBegin = element => Number(decodeURIComponent(element.style.getPropertyValue('--borg-skin')).match(/begin="(-[\d.]+)s"/)[1]);
        for (let index = 0; index < 100 && !decodeURIComponent(panel.style.getPropertyValue('--borg-skin')).includes('attributeName="r"'); index++) ui.advance(500);
        const initialBegin = readBegin(panel);
        ui.advance(625);
        restored = await createInterface({ stored: JSON.parse(ui.window.sessionStorage.getItem(storageKey)) });
        const restoredBegin = readBegin(restored.document.querySelector('.borg-console'));
        assert.ok(Math.abs(restoredBegin - initialBegin + 0.625) < 0.00001);
        assert.equal(restored.window.stuBorgAssimilation.getProgress(), ui.window.stuBorgAssimilation.getProgress());
    } finally { restored?.close(); ui.close(); }
});

const navigationMarkup = `<div class="sidebar" data-layout data-width="120" data-height="500">
    <div id="first-console" class="navigation_item" data-x="10" data-y="20" data-width="94" data-height="60">Kolonien</div>
    <div id="second-console" class="navigation_item" data-x="10" data-y="100" data-width="94" data-height="60">Schiffe</div>
    <div id="third-console" class="navigation_item" data-x="10" data-y="180" data-width="94" data-height="60">Stationen</div>
</div>`;

test('neighboring consoles assimilate at separate times with growing connections between them', async () => {
    const ui = await createInterface({ markup: navigationMarkup });
    try {
        const host = ui.document.querySelector('.sidebar');
        const links = host.style.getPropertyValue('--borg-links');
        assert.match(decodeURIComponent(links), /<animateMotion/);
        assert.match(decodeURIComponent(links), /stroke-dashoffset/);
        assert.equal(host.dataset.borgShell, 'true');
        const started = new Map();
        for (let second = 0; second < 80; second++) {
            ui.advance(1000);
            for (const panel of host.querySelectorAll('.navigation_item')) {
                if (panel.style.getPropertyValue('--borg-skin') !== 'none' && !started.has(panel.id)) started.set(panel.id, second);
            }
        }
        assert.equal(started.size, 3);
        assert.ok(new Set(started.values()).size === 3);
        assert.ok(Math.max(...started.values()) - Math.min(...started.values()) > 10);
        assert.equal(host.style.getPropertyValue('--borg-links'), links);
        assert.equal(ui.rectangleReads, 0);
    } finally { ui.close(); }
});

test('reassimilation on a new page creates a fresh connection timeline', async () => {
    const previous = await createInterface({ elapsed: 150000, markup: navigationMarkup });
    const ui = await createInterface({ restarted: true, stored: JSON.parse(previous.window.sessionStorage.getItem(storageKey)), markup: navigationMarkup });
    try {
        const links = ui.document.querySelector('.sidebar').style.getPropertyValue('--borg-links');
        assert.notEqual(links, previous.document.querySelector('.sidebar').style.getPropertyValue('--borg-links'));
        assert.match(decodeURIComponent(links), /<animateMotion/);
        assert.equal(ui.window.stuBorgAssimilation.getProgress(), 0);
    } finally { previous.close(); ui.close(); }
});

test('reduced motion also removes animated transfers between consoles', async () => {
    const ui = await createInterface({ elapsed: 60000, reduced: true, markup: navigationMarkup });
    try {
        assert.doesNotMatch(decodeURIComponent(ui.document.querySelector('.sidebar').style.getPropertyValue('--borg-links')), /<animate/);
    } finally { ui.close(); }
});

test('hidden consoles do not delay the visible assimilation chain', async () => {
    const plain = await createInterface({ markup: navigationMarkup });
    const hidden = await createInterface({ markup: navigationMarkup.replace('<div id="first-console"', '<div class="navigation_item" data-width="0" data-height="0">Ausgeblendetes Menü</div><div id="first-console"') });
    try {
        plain.advance(7500);
        hidden.advance(7500);
        assert.equal(hidden.document.getElementById('first-console').style.getPropertyValue('--borg-skin'), plain.document.getElementById('first-console').style.getPropertyValue('--borg-skin'));
    } finally { plain.close(); hidden.close(); }
});

test('removing the last consoles also removes their connections from a surviving container', async () => {
    const ui = await createInterface({ elapsed: 150000, markup: navigationMarkup.replace('class="sidebar"', 'class="innerContent"') });
    try {
        const host = ui.document.querySelector('.innerContent');
        assert.match(host.style.getPropertyValue('--borg-links'), /data:image\/svg/);
        host.replaceChildren();
        await Promise.resolve();
        ui.advance(1);
        assert.equal(host.style.getPropertyValue('--borg-links'), 'none');
        assert.equal(host.classList.contains('borg-link-host'), false);
    } finally { ui.close(); }
});

test('the same SVG continues from assimilation into idle animation without a replacement', async () => {
    const ui = await createInterface();
    try {
        const panel = ui.document.querySelector('.borg-console');
        for (let index = 0; index < 100 && !decodeURIComponent(panel.style.getPropertyValue('--borg-skin')).includes('attributeName="r"'); index++) ui.advance(500);
        const image = panel.style.getPropertyValue('--borg-skin');
        ui.advance(150000);
        assert.equal(panel.style.getPropertyValue('--borg-skin'), image);
        assert.equal(panel.style.getPropertyValue('--borg-panel-level'), '1');
        assert.equal(ui.scheduledWork, 0);
    } finally { ui.close(); }
});

test('deassimilation restores the selected theme after a completed assimilation', async () => {
    const theme = ':root { --color-6: #ff0000; --color-31: #660000; --color-28: #300 !important; }';
    const previous = await createInterface({ user: 101, elapsed: 150000, theme });
    const ui = await createInterface({ user: 101, enabled: false, stored: JSON.parse(previous.window.sessionStorage.getItem(storageKey)), theme });
    try {
        const original = previous.document.documentElement;
        assert.equal(original.style.getPropertyValue('--color-6'), '#b9d5ad');
        assert.equal(original.style.getPropertyValue('--color-31'), '#203d17');
        assert.equal(original.style.getPropertyPriority('--color-28'), 'important');
        assert.equal(ui.window.getComputedStyle(ui.document.documentElement).getPropertyValue('--color-6'), '#ff0000');
        assert.equal(ui.document.documentElement.style.getPropertyValue('--color-6'), '');
    } finally { previous.close(); ui.close(); }
});

test('database sections and sensor navigation join the assimilation', async () => {
    const ui = await createInterface({ elapsed: 150000, markup: '<div class="innerContent"><div class="divhead">Datenbank</div><div class="divbody">Fregatten</div><table class="lssarea"><tr><td class="th">32</td></tr></table></div>' });
    try {
        for (const selector of ['.divhead', '.divbody', '.lssarea', 'td.th']) {
            const panel = ui.document.querySelector(selector);
            assert.equal(panel.classList.contains('borg-console'), true);
            assert.match(panel.style.getPropertyValue('--borg-skin'), /data:image\/svg/);
        }
    } finally { ui.close(); }
});

test('a resumed page stays concealed until its console graphics are decoded', async () => {
    const ui = await createInterface({ elapsed: 150000, preparing: true, deferSkins: true });
    try {
        assert.equal(ui.document.documentElement.hasAttribute('data-borg-preparing'), true);
        assert.equal(ui.document.querySelector('.borg-console').style.getPropertyValue('--borg-skin'), '');
        ui.releaseSkins();
        await new Promise(resolve => setTimeout(resolve, 0));
        assert.equal(ui.document.documentElement.hasAttribute('data-borg-preparing'), false);
        assert.match(ui.document.querySelector('.borg-console').style.getPropertyValue('--borg-skin'), /data:image\/svg/);
    } finally { ui.close(); }
});

test('the background contains separate Borg nodes and native data connections', async () => {
    const ui = await createInterface({ elapsed: 150000 });
    try {
        assert.equal(ui.document.querySelectorAll('.borg-backdrop-node').length, 4);
        assert.equal(ui.document.querySelectorAll('.borg-backdrop-frame').length, 4);
        assert.match(decodeURIComponent(ui.document.querySelector('.borg-backdrop').style.getPropertyValue('--borg-background-network')), /<path/);
        assert.equal(ui.rectangleReads, 0);
    } finally { ui.close(); }
});


test('background branches grow at fixed size with staggered native paths that survive timer ticks', async () => {
    const ui = await createInterface({ elapsed: 11000, nativeAnimations: true });
    try {
        const frame = ui.document.querySelector('.borg-backdrop-frame');
        const image = frame.style.backgroundImage;
        const svg = new ui.window.DOMParser().parseFromString(decodeURIComponent(image.slice(image.indexOf('data:') + 'data:image/svg+xml,'.length, image.lastIndexOf('"'))), 'image/svg+xml');
        const paths = [...svg.querySelectorAll('animate[attributeName="stroke-dashoffset"]')];
        assert.ok(paths.length > 30);
        assert.ok(new Set(paths.map(path => path.getAttribute('begin'))).size > 10);
        assert.ok(paths.some(path => Number.parseFloat(path.getAttribute('begin')) < 0));
        assert.ok(paths.some(path => Number.parseFloat(path.getAttribute('begin')) > 0));
        assert.equal(ui.animations.some(({ element }) => element.classList.contains('borg-backdrop-node')), false);
        assert.equal(svg.querySelectorAll('animateTransform').length, 0);
        ui.advance(500);
        assert.equal(frame.style.backgroundImage, image);
        assert.equal(ui.document.querySelectorAll('.borg-backdrop-node[style*="visibility: hidden"]').length, 3);
    } finally { ui.close(); }
});

test('background growth respects reduced motion and resets on reassimilation', async () => {
    const previous = await createInterface({ elapsed: 11000, nativeAnimations: true });
    let ui;
    try {
        const frame = previous.document.querySelector('.borg-backdrop-frame');
        const original = frame.style.backgroundImage;
        previous.setReducedMotion(true);
        const frozen = decodeURIComponent(frame.style.backgroundImage);
        assert.doesNotMatch(frozen, /<animate /);
        assert.match(frozen, /stroke-dashoffset="(?:[1-9]\d?\.\d+)"/);
        previous.setReducedMotion(false);
        assert.match(decodeURIComponent(frame.style.backgroundImage), /attributeName="stroke-dashoffset"/);
        ui = await createInterface({ restarted: true, stored: JSON.parse(previous.window.sessionStorage.getItem(storageKey)), nativeAnimations: true });
        const restarted = ui.document.querySelector('.borg-backdrop-frame');
        assert.equal(restarted.style.backgroundImage, 'none');
        ui.advance(11000);
        assert.notEqual(restarted.style.backgroundImage, original);
        assert.match(decodeURIComponent(restarted.style.backgroundImage), /attributeName="stroke-dashoffset"/);
        assert.equal(ui.rectangleReads, 0);
    } finally { previous.close(); ui?.close(); }
});


test('a deassimilated account ignores saved guest progress and keeps the original interface', async () => {
    const ui = await createInterface({ user: 101, enabled: false, preparing: true, elapsed: 150000, stored: { owner: 'guest', elapsed: 150000, savedAt: 999000 }, theme: ':root{--color-6:red}' });
    try {
        ui.advance(300000);
        assert.equal(ui.document.querySelectorAll('.borg-backdrop, .borg-console, .borg-uplink').length, 0);
        assert.equal(ui.document.documentElement.hasAttribute('data-borg-assimilation'), false);
        assert.equal(ui.document.documentElement.hasAttribute('data-borg-preparing'), false);
        assert.equal(ui.window.stuBorgAssimilation, undefined);
        assert.equal(ui.window.sessionStorage.getItem(storageKey), null);
        assert.equal(ui.window.getComputedStyle(ui.document.documentElement).getPropertyValue('--color-6'), 'red');
        assert.equal(ui.scheduledWork, 0);
    } finally { ui.close(); }
});

test('reassimilation discards saved progress even if the restart has the same server timestamp', async () => {
    const ui = await createInterface({ user: 101, restarted: true, epoch: 1000, stored: { owner: '101', epoch: 1000, elapsed: 150000, savedAt: 1000000 } });
    try {
        assert.equal(ui.window.stuBorgAssimilation.getProgress(), 0);
        assert.equal(ui.document.documentElement.dataset.borgAssimilation, 'dormant');
        ui.advance(15000);
        assert.equal(ui.window.stuBorgAssimilation.getProgress(), .1);
    } finally { ui.close(); }
});


test('assimilation has no floating controls and ignores a legacy manual pause', async () => {
    const ui = await createInterface({ elapsed: 25000, legacyPaused: true });
    try {
        assert.equal(ui.document.documentElement.dataset.borgMotion, 'animated');
        assert.equal(ui.document.querySelectorAll('.borg-uplink, .borg-motion-toggle, .borg-restart').length, 0);
        ui.advance(150000);
        assert.equal(ui.document.querySelectorAll('.borg-uplink, .borg-motion-toggle, .borg-restart').length, 0);
    } finally { ui.close(); }
});
