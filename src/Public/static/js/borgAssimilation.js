(() => {
    'use strict';

    const configElement = document.getElementById('borg-assimilation-config');
    if (!configElement || window.stuBorgAssimilation) return;

    const config = JSON.parse(configElement.textContent);
    if (config.enabled === false) {
        try {
            const stored = JSON.parse(sessionStorage.getItem('stu:anniversary:25:borg'));
            if (stored && (stored.owner === String(config.user) || stored.owner === 'guest')) sessionStorage.removeItem('stu:anniversary:25:borg');
        } catch {}
        document.documentElement.removeAttribute('data-borg-preparing');
        return;
    }
    const root = document.documentElement;
    root.dataset.borgSurface = config.surface === 'public' ? 'public' : 'game';
    const duration = Math.max(1000, Number(config.duration) || 150000);
    const storageKey = 'stu:anniversary:25:borg';
    const owner = config.user === null ? 'guest' : String(config.user);
    const clock = () => Date.now();
    const clamp = value => Math.min(1, Math.max(0, value));
    let elapsed = Math.min(duration, Math.max(0, Number(config.elapsed) || 0));

    try {
        const stored = JSON.parse(sessionStorage.getItem(storageKey));
        const sameTimeline = !config.epoch || !stored?.epoch || stored.epoch === config.epoch;
        if (!config.restarted && stored && sameTimeline && (stored.owner === owner || (stored.owner === 'guest' && owner !== 'guest'))
            && Number.isFinite(stored.elapsed) && Number.isFinite(stored.savedAt)) {
            elapsed = Math.min(duration, Math.max(elapsed, stored.elapsed + Math.max(0, clock() - stored.savedAt)));
        }
    } catch {}

    const startedAt = clock() - elapsed;
    let furthestElapsed = elapsed;
    const progress = () => {
        furthestElapsed = Math.max(furthestElapsed, clock() - startedAt);
        return clamp(furthestElapsed / duration);
    };
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const palette = getComputedStyle(root);
    const textColors = new Set([2, 3, 4, 5, 6, 9, 10, 12, 23, 27, 30, 35, 36, 40, 46, 52, 54, 55, 56, 57, 58, 60, 62]);
    const borderColors = new Set([1, 8, 16, 18, 22, 53, 63]);
    const baseColors = [];
    const rootOverrides = new Map();
    let rootPaletteComplete = false;
    let initialized = false;
    const nativeColors = Boolean(window.CSS?.registerProperty && root.animate);

    for (let index = 1; index <= 63; index++) {
        let original = palette.getPropertyValue(`--color-${index}`).replace(/!\s*important/g, '').trim();
        if (!original) continue;
        if (original === 'inherit') original = palette.getPropertyValue('--color-6').trim();
        const target = textColors.has(index) ? (index === 60 ? '#ddffb4' : '#b9d5ad')
            : borderColors.has(index) ? '#496b34'
                : [11, 20, 25, 26, 31, 38, 42, 43].includes(index) ? '#203d17' : '#091109';
        baseColors.push([`--color-${index}`, original, target]);
        rootOverrides.set(`--color-${index}`, [root.style.getPropertyValue(`--color-${index}`), root.style.getPropertyPriority(`--color-${index}`)]);
    }
    for (const [property, original] of baseColors) {
        root.style.setProperty(`--borg-base-${property.slice(2)}`, original);
    }

    const panelPalette = document.createElement('style');
    panelPalette.textContent = `.borg-console{${baseColors.map(([property, original, target]) => {
        const index = Number(property.slice(8));
        const tone = textColors.has(index) ? 'var(--borg-text-tone)' : borderColors.has(index) ? 'var(--borg-edge-tone)' : target;
        return `${property}:color-mix(in srgb,${original} calc(100% - var(--borg-mix)),${tone});`;
    }).join('')}}`;
    document.head.appendChild(panelPalette);

    function save() {
        try {
            sessionStorage.setItem(storageKey, JSON.stringify({ owner, epoch: config.epoch, elapsed: progress() * duration, savedAt: clock() }));
        } catch {}
    }

    function updateTheme() {
        const current = progress();
        const complete = current >= 1;
        if (initialized && rootPaletteComplete !== complete) {
            rootPaletteComplete = complete;
            for (const [property, , target] of baseColors) {
                const [original, priority] = rootOverrides.get(property);
                if (complete) root.style.setProperty(property, target, 'important');
                else if (original) root.style.setProperty(property, original, priority);
                else root.style.removeProperty(property);
            }
        }
        const stage = current >= 1 ? 'complete' : current > 0.02 ? 'active' : 'dormant';
        if (root.dataset.borgAssimilation !== stage) root.dataset.borgAssimilation = stage;
    }

    updateTheme();
    save();
    window.stuBorgAssimilation = Object.freeze({ getProgress: progress });
    window.addEventListener('pagehide', save);

    function initialize() {
        const backdrop = document.createElement('div');
        backdrop.className = 'borg-backdrop';
        backdrop.setAttribute('aria-hidden', 'true');
        document.body.prepend(backdrop);
        const panels = new Map();
        const visible = new Set();
        const families = ['conduits', 'neural', 'armour', 'glyphs', 'lattice', 'transwarp'];
        const tones = {
            conduits: ['#07120c', '#29452c', '#579c4d', '#b4e35d', '#9eac69'],
            neural: ['#061813', '#235548', '#29a378', '#91e3ad', '#91c94c'],
            armour: ['#171b16', '#3c4539', '#87946c', '#c5d38e', '#bc9854'],
            glyphs: ['#121a08', '#3b4d1b', '#79ab24', '#d2ee6a', '#d3bc57'],
            lattice: ['#071711', '#23513b', '#327948', '#6cdaa0', '#8caa6e'],
            transwarp: ['#0c190c', '#354b2e', '#739e4c', '#b1ed9d', '#bea258']
        };
        const selector = [
            '#banner', '.sidebar', '.header', '.nav_box_content', '.header_box', '.navigation_item', '.navigation_item_servertime', '.box', '.box_title',
            'table.tcal', 'table.use', 'table.lssarea', '.divhead', '.divbody', '.ttop', 'td.th', 'th:first-child', '.mainbar_box_header', '.mainbar_nav_box_header', '.sidebar_box_header',
            '.mainbar_box_content', '.sidebar_box_content', '.registration_box_content', '.news_box_content',
            '.link-box', '.modal-content', '.dropdown-menu', '.userStarmapPanel', '.stu-border-faction',
            '.card', '.btn-stu', 'input.form-control', '.stu-select'
        ].join(', ');
        const hostSelector = '#popupWindow, .modal-content, .dropdown-menu, .sidebar, .header, .innerContent, body > .container';
        const hosts = new Map();
        let graphDirty = true;
        let graphRevision = 0;
        let timer = null;
        let refreshFrame = null;
        let dragging = false;
        let backgroundKey = '';
        let backgroundAnimation = null;
        const pendingSkins = new Set();
        let preparing = root.hasAttribute('data-borg-preparing');
        let intersectionReady = false;
        const backgroundNodes = [
            { x: 0.12, y: 0.24, radius: 100, onset: 0.04, family: 'neural' },
            { x: 0.87, y: 0.38, radius: 138, onset: 0.19, family: 'glyphs' },
            { x: 0.73, y: 0.79, radius: 178, onset: 0.4, family: 'transwarp' },
            { x: 0.23, y: 0.76, radius: 126, onset: 0.62, family: 'conduits' }
        ];
        let backgroundNetworkKey = '';

        function hash(value) {
            let result = 2166136261;
            for (let index = 0; index < value.length; index++) result = Math.imul(result ^ value.charCodeAt(index), 16777619);
            return result >>> 0;
        }

        function identity(element) {
            const own = element.id || element.querySelector('img[id]')?.id || element.querySelector('a[href]')?.getAttribute('href');
            if (own) return own;
            const path = [];
            let node = element;
            for (let depth = 0; node && depth < 5; depth++, node = node.parentElement) {
                if (node.id) { path.push(node.id); break; }
                path.push(`${node.tagName}:${Array.prototype.indexOf.call(node.parentElement?.children || [], node)}`);
            }
            return `${path.join('/')}:${element.className}:${element.textContent.trim().slice(0, 24)}`;
        }

        function rememberBackground(element) {
            if (element.style.getPropertyValue('--borg-original-image')) return;
            const original = getComputedStyle(element);
            for (const [property, value] of [
                ['image', original.backgroundImage || 'none'], ['size', original.backgroundSize || 'auto'],
                ['position', original.backgroundPosition || '0% 0%'], ['repeat', original.backgroundRepeat || 'repeat'],
                ['origin', original.backgroundOrigin || 'padding-box'], ['clip', original.backgroundClip || 'border-box'],
                ['color', original.backgroundColor || 'transparent'], ['attachment', original.backgroundAttachment || 'scroll']
            ]) element.style.setProperty(`--borg-original-${property}`, value);
        }

        function coordinates(element) {
            let x = 0;
            let y = 0;
            for (let node = element; node; node = node.offsetParent) {
                x += node.offsetLeft || 0;
                y += node.offsetTop || 0;
            }
            return { x, y };
        }

        function ports(from, to) {
            const a = from.bounds;
            const b = to.bounds;
            const fraction = 0.15 + to.seed % 71 / 100;
            if (a.y + a.height <= b.y || b.y + b.height <= a.y) {
                const top = a.y < b.y;
                const left = Math.max(a.x, b.x);
                const right = Math.min(a.x + a.width, b.x + b.width);
                const x = right > left ? left + (right - left) * fraction : a.x + a.width / 2;
                return [{ x, y: top ? a.y + a.height : a.y }, {
                    x: right > left ? x : b.x + b.width / 2, y: top ? b.y : b.y + b.height
                }];
            }
            if (a.x + a.width <= b.x || b.x + b.width <= a.x) {
                const left = a.x < b.x;
                const top = Math.max(a.y, b.y);
                const bottom = Math.min(a.y + a.height, b.y + b.height);
                const y = bottom > top ? top + (bottom - top) * fraction : a.y + a.height / 2;
                return [{ x: left ? a.x + a.width : a.x, y }, {
                    x: left ? b.x : b.x + b.width, y: bottom > top ? y : b.y + b.height / 2
                }];
            }
            return null;
        }

        function rebuildGraph() {
            graphDirty = false;
            graphRevision++;
            for (const host of hosts.values()) host.panels = [];
            const positions = new Map();
            const position = element => {
                if (!positions.has(element)) positions.set(element, coordinates(element));
                return positions.get(element);
            };
            for (const panel of panels.values()) {
                const element = panel.element.closest(hostSelector) || document.body;
                let host = hosts.get(element);
                if (!host) {
                    rememberBackground(element);
                    element.classList.add('borg-link-host');
                    host = { element, panels: [], edges: [], key: '' };
                    hosts.set(element, host);
                }
                const origin = position(element);
                const own = position(panel.element);
                panel.bounds = { x: own.x - origin.x, y: own.y - origin.y, width: panel.width, height: panel.height };
                panel.host = host;
                panel.parent = panels.get(panel.element.parentElement?.closest('.borg-console'));
                if (panel.parent?.shell || panel.parent?.element.closest(hostSelector) !== element) panel.parent = null;
                host.panels.push(panel);
            }
            for (const [element, host] of hosts) {
                if (!element.isConnected || !host.panels.length) {
                    element.style.setProperty('--borg-links', 'none');
                    element.classList.remove('borg-link-host');
                    hosts.delete(element);
                    continue;
                }
                host.width = element.offsetWidth;
                host.height = element.offsetHeight;
                const roots = host.panels.filter(panel => !panel.parent && !panel.shell && panel.width > 0 && panel.height > 0);
                const remaining = roots.filter(panel => !panel.scheduled);
                const visited = roots.filter(panel => panel.scheduled);
                const continued = visited.length > 0;
                const navigation = element.matches('.sidebar');
                const header = element.matches('.header');
                const base = navigation ? 0.03 : header ? 0.08 : element.matches('.innerContent') ? 0.13 : 0.035;
                const spread = navigation ? 0.4 : header ? 0.4 : 0.77;
                const total = remaining.reduce((sum, panel) => sum + 1 + panel.seed % 17 / 8, 0);
                let weight = 0;
                while (remaining.length) {
                    let selected = 0;
                    let source = null;
                    let score = Infinity;
                    for (let i = 0; i < remaining.length; i++) {
                        const candidate = remaining[i];
                        const neighbors = visited.length ? visited.slice(-12) : [null];
                        for (const previous of neighbors) {
                            const distance = previous
                                ? Math.hypot(candidate.bounds.x - previous.bounds.x, candidate.bounds.y - previous.bounds.y)
                                : Math.max(0, candidate.bounds.y) + candidate.seed % 29;
                            if (distance < score) { score = distance; selected = i; source = previous; }
                        }
                    }
                    const panel = remaining.splice(selected, 1)[0];
                    panel.onset = continued
                        ? Math.min(0.88, (source?.onset || base) + (source?.span || 0.04) * 0.7 + panel.seed % 13 / 1000)
                        : base + (total ? weight / total : 0) * spread;
                    if (source && source.onset >= panel.onset) source = visited.find(previous => previous.onset < panel.onset - 0.002) || null;
                    panel.source = source;
                    panel.scheduled = true;
                    weight += 1 + panel.seed % 17 / 8;
                    visited.push(panel);
                }
                for (const panel of host.panels) {
                    if (panel.shell) {
                        panel.onset = base;
                        panel.span = spread + 0.075;
                    } else if (panel.parent) {
                        panel.onset = panel.parent.onset + (panel.heading ? 0 : 0.003 + panel.seed % 7 / 1000);
                        panel.span = panel.parent.span * (panel.heading ? 1 : 0.75);
                    }
                    panel.entry = { x: 0.08 + panel.seed % 83 / 100, y: 0.08 + (panel.seed >>> 8) % 83 / 100 };
                    panel.entries = [];
                }
                host.edges = [];
                for (const panel of roots) {
                    const sources = panel.source && roots.includes(panel.source) ? [panel.source] : [];
                    if (panel.seed % 3 === 0) {
                        const nearby = roots.filter(previous => previous !== panel.source && previous.onset < panel.onset - 0.003)
                            .map(previous => ({ panel: previous, endpoints: ports(previous, panel) }))
                            .filter(candidate => candidate.endpoints)
                            .map(candidate => ({ ...candidate, distance: Math.hypot(candidate.endpoints[1].x - candidate.endpoints[0].x, candidate.endpoints[1].y - candidate.endpoints[0].y) }))
                            .filter(candidate => candidate.distance < 180)
                            .sort((a, b) => a.distance - b.distance);
                        if (nearby.length) sources.push(nearby[0].panel);
                    }
                    for (const source of sources) {
                        const endpoints = ports(source, panel);
                        if (!endpoints) continue;
                        const [start, end] = endpoints;
                        if (Math.hypot(end.x - start.x, end.y - start.y) > 520) continue;
                        const entry = { x: clamp((end.x - panel.bounds.x) / Math.max(1, panel.width)), y: clamp((end.y - panel.bounds.y) / Math.max(1, panel.height)) };
                        if (source === panel.source) panel.entry = entry;
                        else panel.entries.push(entry);
                        const bend = (panel.seed % 2 ? -1 : 1) * (8 + panel.seed % 19);
                        const vertical = Math.abs(end.y - start.y) >= Math.abs(end.x - start.x);
                        const path = vertical
                            ? `M${start.x} ${start.y} C${start.x + bend} ${start.y + (end.y - start.y) * 0.3} ${end.x - bend} ${end.y - (end.y - start.y) * 0.3} ${end.x} ${end.y}`
                            : `M${start.x} ${start.y} C${start.x + (end.x - start.x) * 0.3} ${start.y + bend} ${end.x - (end.x - start.x) * 0.3} ${end.y - bend} ${end.x} ${end.y}`;
                        const arrival = panel.onset + (source === panel.source ? 0 : panel.span * 0.12);
                        const onset = Math.max(source.onset + 0.001, arrival - 0.01 - panel.seed % 8 / 1000);
                        host.edges.push({ from: source, to: panel, start, end, path, onset, span: Math.max(0.001, arrival - onset) });
                    }
                }
                for (const panel of host.panels) {
                    const geometry = `${panel.width}:${panel.height}:${panel.onset}:${panel.span}:${panel.entry.x}:${panel.entry.y}:${JSON.stringify(panel.entries)}`;
                    if (geometry === panel.geometry) continue;
                    panel.geometry = geometry;
                    panel.key = '';
                    panel.animation?.cancel();
                    panel.animation = null;
                }
                host.key = '';
            }
        }

        function renderConnections(current, motion) {
            for (const host of hosts.values()) {
                const edges = host.edges.filter(edge => visible.has(edge.from) || visible.has(edge.to)).slice(0, 64);
                const key = `${graphRevision}:${motion}:${motion ? 'active' : current.toFixed(4)}:${edges.map(edge => `${edge.from.seed}>${edge.to.seed}`).join(',')}`;
                if (host.key === key || !host.width || !host.height) continue;
                host.key = key;
                const tiles = new Map();
                for (const edge of edges) {
                    const age = clamp((current - edge.onset) / edge.span);
                    const [, , trace, bright] = tones[edge.to.family];
                    const animate = (attribute, from, to) => motion && age < 1 ? `<animate attributeName="${attribute}" from="${from}" to="${to}" dur="${edge.span * duration / 1000}s" begin="${(edge.onset - current) * duration / 1000}s" fill="freeze"/>` : '';
                    const packet = motion && age < 1 ? `<g opacity="0"><animateMotion path="${edge.path}" dur="${edge.span * duration / 1000}s" begin="${(edge.onset - current) * duration / 1000}s" fill="freeze"/><animate attributeName="opacity" values="0;1;1;0" keyTimes="0;.12;.8;1" dur="${edge.span * duration / 1000}s" begin="${(edge.onset - current) * duration / 1000}s" fill="freeze"/><circle r="3.5" fill="${bright}" opacity=".18"/><circle r="1.3" fill="${bright}"/></g>` : '';
                    const path = `<path d="${edge.path}" pathLength="100" fill="none" stroke="#020603" stroke-width="4" stroke-dasharray="100" stroke-dashoffset="${100 * (1 - age)}">${animate('stroke-dashoffset', 100, 0)}</path><path d="${edge.path}" pathLength="100" fill="none" stroke="${trace}" stroke-width="1.4" stroke-dasharray="100" stroke-dashoffset="${100 * (1 - age)}">${animate('stroke-dashoffset', 100, 0)}</path><circle cx="${edge.start.x}" cy="${edge.start.y}" r="3" fill="#071109" stroke="${bright}" opacity="${age * 0.75}">${animate('opacity', 0, 0.75)}</circle><circle cx="${edge.end.x}" cy="${edge.end.y}" r="${2 + age * 3}" fill="#071109" stroke="${bright}" stroke-width="1.1" opacity="${age * 0.8}">${animate('r', 2, 5)}${animate('opacity', 0, 0.8)}</circle>${packet}`;
                    const first = Math.max(0, Math.floor((Math.min(edge.start.y, edge.end.y) - 32) / 1024));
                    const last = Math.min(Math.ceil(host.height / 1024) - 1, Math.floor((Math.max(edge.start.y, edge.end.y) + 32) / 1024));
                    for (let tile = first; tile <= last; tile++) tiles.set(tile, (tiles.get(tile) || '') + path);
                }
                const images = [];
                const sizes = [];
                const positions = [];
                for (const [tile, paths] of tiles) {
                    const height = Math.min(1024, host.height - tile * 1024);
                    const svg = `<svg xmlns="http://www.w3.org/2000/svg" data-timeline="${startedAt}" width="${host.width}" height="${height}" viewBox="0 ${tile * 1024} ${host.width} ${height}">${paths}</svg>`;
                    images.push(`url("data:image/svg+xml,${encodeURIComponent(svg)}")`);
                    sizes.push(`100% ${height}px`);
                    positions.push(`0 ${tile * 1024}px`);
                }
                host.element.style.setProperty('--borg-links', images.join(',') || 'none');
                host.element.style.setProperty('--borg-links-size', sizes.join(',') || '100% 100%');
                host.element.style.setProperty('--borg-links-position', positions.join(',') || '0 0');
                host.element.style.setProperty('--borg-links-repeat', images.map(() => 'no-repeat').join(',') || 'no-repeat');
                host.element.style.setProperty('--borg-links-attachment', images.map(() => 'local').join(',') || 'local');
                host.element.style.setProperty('--borg-links-origin', images.map(() => 'border-box').join(',') || 'border-box');
            }
        }

        const line = (d, color = '#538c35', width = 1) => `<path d="${d}" fill="none" stroke="${color}" stroke-width="${width}"/>`;
        const box = (x, y, w, h, color = '#507339') => `<rect x="${x}" y="${y}" width="${Math.max(0, w)}" height="${Math.max(0, h)}" fill="${color}"/>`;
        const circle = (x, y, r, color = '#7ec94a', width = 1) => `<circle cx="${x}" cy="${y}" r="${r}" fill="none" stroke="${color}" stroke-width="${width}"/>`;
        const glyph = (x, y, seed, radius = 4) => `<g transform="translate(${x} ${y}) rotate(${seed % 4 * 90})">${line(`M ${-radius} 0 H 1 V ${-radius}`, '#a0dc69', 1.5)}${circle(0, 0, radius, '#68ae3c', 2)}${box(1, 0, radius + 1, 2, '#071005')}${box(1, 2, radius, 1, '#a3cf69')}</g>`;

        function backdropTrace(path, color, width, onset, span, current, motion) {
            const age = clamp((current - onset) / span);
            const animation = motion && age < 1 ? `<animate attributeName="stroke-dashoffset" from="100" to="0" dur="${span * duration / 1000}s" begin="${(onset - current) * duration / 1000}s" fill="freeze"/>` : '';
            return `<path d="${path}" fill="none" stroke="${color}" stroke-width="${width}" pathLength="100" stroke-dasharray="100" stroke-dashoffset="${100 * (1 - age)}">${animation}</path>`;
        }

        function backdropAppearance(content, onset, span, current, motion) {
            const age = clamp((current - onset) / span);
            const animation = motion && age < 1 ? `<animate attributeName="opacity" from="0" to="1" dur="${span * duration / 1000}s" begin="${(onset - current) * duration / 1000}s" fill="freeze"/>` : '';
            return `<g opacity="${age}">${animation}${content}</g>`;
        }

        function backdropArtwork(node, index, current, motion) {
            const [surface, , trace, bright, data] = tones[node.family];
            const appear = (content, start, span = .07) => backdropAppearance(content, node.onset + start * node.span, span * node.span, current, motion);
            const line = (path, color = trace, width = 1, start = .12, span = .24) => backdropTrace(path, color, width, node.onset + start * node.span, span * node.span, current, motion);
            const ring = (x, y, radius, color = trace, width = 1, start = .1, span = .27) => line(`M${x} ${y - radius} A${radius} ${radius} 0 1 1 ${x - .001} ${y - radius}`, color, width, start, span);
            const mark = (x, y, seed, radius, start = .2) => appear(glyph(x, y, seed, radius), start);
            const orb = (x, y, radius, start = 0) => appear(`<circle cx="${x}" cy="${y}" r="${radius * 1.8}" fill="url(#halo)"/><circle cx="${x}" cy="${y}" r="${radius}" fill="url(#orb)" stroke="${trace}" stroke-width=".8"/>`, start, .09) + ring(x, y, radius * .72, data, .6, start + .01, .08) + ring(x, y, radius * .22, bright, .8, start + .03, .06);
            const braid = (x1, y1, x2, y2, bend, waves = 5, start = .08, span = .25) => {
                const dx = x2 - x1;
                const dy = y2 - y1;
                const length = Math.hypot(dx, dy) || 1;
                return [0, Math.PI].map((phase, ribbon) => {
                    let path = '';
                    for (let step = 0; step <= 48; step++) {
                        const t = step / 48;
                        const offset = Math.sin(t * Math.PI) * bend + Math.sin(t * Math.PI * waves * 2 + phase) * 2.1;
                        path += `${step ? 'L' : 'M'}${(x1 + dx * t - dy / length * offset).toFixed(2)} ${(y1 + dy * t + dx / length * offset).toFixed(2)} `;
                    }
                    return line(path, ribbon ? data : trace, ribbon ? .7 : 1.3, start + ribbon * .008, span);
                }).join('');
            };
            const symbols = (x, y, columns, rows, seed, start = .3) => {
                let result = '';
                for (let row = 0; row < rows; row++) for (let column = 0; column < columns; column++) {
                    if ((row * 3 + column + seed) % 7 === 0) continue;
                    result += mark(x + column * 13, y + row * 13, row * 7 + column + seed, 2 + (row + column) % 2, start + row * .025 + column * .012);
                }
                return result;
            };
            let frame = '';
            let glow = '';
            const signals = ['', '', ''];
            if (index === 0) {
                frame += `<mask id="rim">${ring(128, 128, 116, 'white', 4, .19, .4)}</mask><circle cx="128" cy="128" r="116" fill="none" stroke="${trace}" stroke-width=".6" stroke-dasharray="144 19 61 28 8 33" mask="url(#rim)"/>${ring(128, 128, 105, data, .5, .2, .36)}`;
                for (let tick = 0; tick < 47; tick++) {
                    const angle = tick / 47 * Math.PI * 2;
                    const radius = tick % 4 ? 112 : 108;
                    frame += line(`M${128 + Math.cos(angle) * radius} ${128 + Math.sin(angle) * radius} L${128 + Math.cos(angle) * 116} ${128 + Math.sin(angle) * 116}`, trace, .8, .21 + tick / 47 * .38, .035);
                }
                const satellites = [[61, 71, 10], [108, 41, 6], [181, 64, 16], [213, 123, 9], [189, 192, 11], [135, 211, 5], [70, 177, 15], [39, 128, 7], [105, 84, 5]];
                satellites.forEach(([x, y, radius], satellite) => {
                    const path = `M128 128 C${128 + (x - 128) * .1} ${y} ${x + (satellite % 2 ? 19 : -19)} 128 ${x} ${y}`;
                    const start = .035 + satellite * .027;
                    const arrival = start + .16 + satellite % 3 * .016;
                    frame += line(path, trace, 1, start, arrival - start) + orb(x, y, radius, arrival);
                    signals[satellite % 3] += line(path, bright, 1.5, start, arrival - start) + ring(x, y, radius + 3, bright, .8, arrival, .08);
                });
                frame += orb(128, 128, 25) + mark(128, 128, 1, 9, .025) + symbols(76, 114, 2, 3, 2) + symbols(153, 136, 2, 2, 1);
                glow = appear(`<circle cx="128" cy="128" r="56" fill="url(#halo)"/>`, .02, .12);
            } else if (index === 1) {
                frame += braid(128, 128, 31, 218, 28, 6) + braid(128, 128, 217, 27, 21, 7) + braid(128, 128, 224, 172, -20);
                const satellites = [[46, 72, 19], [71, 143, 10], [111, 206, 16], [185, 88, 11], [220, 173, 22]];
                satellites.forEach(([x, y, radius], satellite) => {
                    const arrival = .25 + satellite * .037;
                    const connection = braid(128, 128, x, y, satellite % 2 ? 16 : -11, 3, arrival - .2, .2);
                    frame += connection + orb(x, y, radius, arrival);
                    signals[satellite % 3] += connection + ring(x, y, radius * .8, bright, 1.4, arrival, .08);
                });
                frame += orb(128, 128, 17) + symbols(161, 17, 4, 3, 4) + symbols(21, 167, 3, 4, 1) + symbols(142, 181, 2, 5, 3);
                signals[1] += symbols(161, 17, 4, 3, 4);
                signals[2] += symbols(21, 167, 3, 4, 1);
                glow = appear(`<circle cx="46" cy="72" r="36" fill="url(#halo)"/>`, .25, .12) + appear(`<circle cx="220" cy="173" r="39" fill="url(#halo)"/>`, .398, .12);
            } else if (index === 2) {
                frame += `<mask id="rim">${ring(128, 128, 72, 'white', 5, .04, .24)}</mask><circle cx="128" cy="128" r="72" fill="none" stroke="${trace}" stroke-width="2" stroke-dasharray="112 13 47 28 86 42" mask="url(#rim)"/>${ring(128, 128, 64, data, .8, .025, .22)}${ring(128, 128, 49, trace, 1.5, .01, .2)}${orb(128, 128, 27)}`;
                frame += line('M101 128 H18 M155 128 H246 M128 101 V22 M128 155 V230', data, 1.2);
                const satellites = [[34, 43, 9], [91, 19, 5], [212, 61, 17], [233, 150, 6], [189, 220, 12], [44, 198, 18]];
                satellites.forEach(([x, y, radius], satellite) => {
                    const angle = Math.atan2(y - 128, x - 128);
                    const startX = 128 + Math.cos(angle) * 65;
                    const startY = 128 + Math.sin(angle) * 65;
                    const path = `M${startX} ${startY} Q${x + (satellite % 2 ? -34 : 27)} ${startY} ${x} ${y}`;
                    const start = .16 + satellite * .03;
                    frame += line(path, trace, 1.3, start, .19) + orb(x, y, radius, start + .19);
                    signals[satellite % 3] += line(path, bright, 1.6, start, .19) + mark(x, y, satellite, radius * .35, start + .2);
                });
                frame += line('M156 183 H240 V239 H153 V207 M159 189 H233 M159 232 H233', trace, .7) + symbols(165, 200, 5, 2, 3) + symbols(11, 91, 3, 2, 4);
                signals[2] += symbols(165, 200, 5, 2, 3);
                glow = appear(`<circle cx="128" cy="128" r="83" fill="url(#halo)"/>`, .02, .12);
            } else {
                frame += braid(92, 69, 172, 179, 28, 9, .03, .23) + braid(92, 69, 60, 234, -37, 10, .06, .32) + braid(172, 179, 231, 33, 22, 7, .26, .23);
                frame += orb(92, 69, 33) + orb(172, 179, 21, .26) + ring(92, 69, 43, trace, .7, .02, .2) + ring(172, 179, 28, data, .8, .27, .15);
                const satellites = [[38, 32, 9, 92, 69], [49, 120, 15, 92, 69], [29, 186, 11, 60, 171], [113, 217, 8, 172, 179], [219, 109, 13, 206, 125], [225, 225, 6, 172, 179]];
                satellites.forEach(([x, y, radius, fromX, fromY], satellite) => {
                    const start = satellite < 2 ? .08 + satellite * .035 : .28 + (satellite - 2) * .025;
                    const connection = braid(fromX, fromY, x, y, satellite % 2 ? 9 : -12, 3, start, .16);
                    frame += connection + orb(x, y, radius, start + .16);
                    signals[satellite % 3] += connection + ring(x, y, radius * .6, bright, 1.1, start + .16, .07);
                });
                frame += symbols(153, 31, 3, 5, 1) + symbols(71, 147, 3, 3, 2) + mark(92, 69, 3, 12, .025);
                signals[0] += symbols(153, 31, 3, 5, 1);
                glow = appear(`<circle cx="92" cy="69" r="58" fill="url(#halo)"/>`, .02, .12) + appear(`<circle cx="172" cy="179" r="44" fill="url(#halo)"/>`, .26, .12);
            }
            const extensions = [
                [[181, 64, 327, -21, 10], [213, 123, 397, 181, 7], [70, 177, -74, 311, 15], [135, 211, 208, 381, 6]],
                [[46, 72, -155, -89, 13], [111, 206, -67, 372, 8], [220, 173, 318, 286, 11], [185, 88, 229, -139, 7]],
                [[34, 43, -97, 11, 8], [44, 198, -128, 332, 12], [91, 19, 26, -134, 6], [212, 61, 355, -48, 13], [233, 150, 379, 227, 8]],
                [[38, 32, -80, -129, 11], [49, 120, -158, 153, 17], [113, 217, 187, 386, 7], [219, 109, 361, -84, 12]]
            ][index];
            extensions.forEach(([fromX, fromY, x, y, radius], branch) => {
                const start = .46 + branch * .05;
                const arrival = start + .22;
                const path = braid(fromX, fromY, x, y, branch % 2 ? 21 : -29, 5 + branch, start, .22);
                frame += `<g opacity=".65">${path}${orb(x, y, radius, arrival)}${mark(x + radius + 10, y + 3, index + branch, 3, arrival + .04)}</g>`;
                signals[branch % 3] += `<g opacity=".65">${path}${ring(x, y, radius * .8, bright, .8, arrival, .08)}</g>`;
            });
            const bounds = {
                x: Math.min(0, ...extensions.map(extension => extension[2] - 60)),
                y: Math.min(0, ...extensions.map(extension => extension[3] - 60)),
                right: Math.max(256, ...extensions.map(extension => extension[2] + 60)),
                bottom: Math.max(256, ...extensions.map(extension => extension[3] + 60))
            };
            const svg = content => `<svg xmlns="http://www.w3.org/2000/svg" data-timeline="${startedAt}" viewBox="${bounds.x} ${bounds.y} ${bounds.right - bounds.x} ${bounds.bottom - bounds.y}"><defs><radialGradient id="orb" cx="43%" cy="39%"><stop stop-color="${bright}" stop-opacity=".75"/><stop offset=".25" stop-color="${data}" stop-opacity=".48"/><stop offset=".76" stop-color="${trace}" stop-opacity=".3"/><stop offset="1" stop-color="${surface}" stop-opacity=".18"/></radialGradient><radialGradient id="halo"><stop stop-color="${trace}" stop-opacity=".21"/><stop offset=".45" stop-color="${trace}" stop-opacity=".09"/><stop offset="1" stop-color="${trace}" stop-opacity="0"/></radialGradient></defs>${content}</svg>`;
            return { frame: svg(`<g opacity=".65">${frame}</g>`), glow: svg(glow), signals: signals.map(svg), bounds };
        }

        function initializeBackdrop() {
            backgroundNodes.forEach((node, index) => {
                const element = document.createElement('div');
                element.className = 'borg-backdrop-node';
                element.dataset.borgTopology = node.family;
                element.style.left = `${node.x * 100}%`;
                element.style.top = `${node.y * 100}%`;
                element.style.width = element.style.height = `${node.radius * 2}px`;
                element.style.setProperty('--borg-pulse-duration', `${13 + index * 4.7}s`);
                element.style.setProperty('--borg-background-clock', `${-progress() * duration / 1000}s`);
                node.activity = document.createElement('span');
                node.activity.className = 'borg-backdrop-activity';
                element.appendChild(node.activity);
                node.layers = ['frame', 'core', 'signal', 'signal', 'signal'].map((type, signal) => {
                    const layer = document.createElement('span');
                    layer.className = `borg-backdrop-${type}`;
                    if (type === 'signal') {
                        layer.style.setProperty('--borg-signal-duration', `${17.3 + index * 5.7 + (signal - 2) * 7.1}s`);
                        layer.style.setProperty('--borg-signal-delay', `${-(progress() * duration / 1000 + index * 8.9 + (signal - 2) * 13.7)}s`);
                    }
                    (type === 'signal' ? node.activity : element).appendChild(layer);
                    return layer;
                });
                backdrop.appendChild(element);
                node.element = element;
                node.span = 0.09 + index * 0.015;
                if (node.activity.animate) node.activation = node.activity.animate([{ opacity: 0 }, { opacity: 1 }], {
                    duration: 1200, delay: (node.onset + node.span) * duration, fill: 'both'
                });
                node.packet = document.createElement('span');
                node.packet.className = 'borg-backdrop-packet';
                backdrop.appendChild(node.packet);
            });
        }

        function updateBackdropNetwork(current, motion) {
            const w = window.innerWidth;
            const h = window.innerHeight;
            const key = `${w}:${h}:${motion}:${startedAt}:${motion ? 'live' : current.toFixed(3)}`;
            if (backgroundNetworkKey === key) return;
            backgroundNetworkKey = key;
            let network = '';
            backgroundNodes.forEach((node, index) => {
                const from = backgroundNodes[(index + 3) % backgroundNodes.length];
                const x = from.x * w;
                const y = from.y * h;
                const targetX = node.x * w;
                const targetY = node.y * h;
                const bend = (index % 2 ? -1 : 1) * Math.min(170, w * 0.12);
                const path = `M${x} ${y} C${x + bend} ${y - h * 0.12} ${targetX - bend} ${targetY + h * 0.1} ${targetX} ${targetY}`;
                const onset = Math.min(from.onset, node.onset);
                const span = Math.max(0.06, Math.max(from.onset, node.onset) + 0.04 - onset);
                const age = clamp((current - onset) / span);
                const [, , trace, bright, data] = tones[node.family];
                const draw = motion && age < 1 ? `<animate attributeName="stroke-dashoffset" from="100" to="0" dur="${span * duration / 1000}s" begin="${(onset - current) * duration / 1000}s" fill="freeze"/>` : '';
                network += `<g opacity=".28"><path d="${path}" fill="none" stroke="${trace}" stroke-width="1.2" pathLength="100" stroke-dasharray="100" stroke-dashoffset="${100 * (1 - age)}">${draw}</path><path d="${path}" transform="translate(${index % 2 ? 5 : -5} 4)" fill="none" stroke="${data}" stroke-width=".5" pathLength="100" stroke-dasharray="100" stroke-dashoffset="${100 * (1 - age)}">${draw}</path>${circle(targetX, targetY, 8, bright, .8)}</g>`;
                for (let branch = 0; branch < 5; branch++) {
                    const t = .16 + branch * .165;
                    const inverse = 1 - t;
                    const startX = inverse ** 3 * x + 3 * inverse ** 2 * t * (x + bend) + 3 * inverse * t ** 2 * (targetX - bend) + t ** 3 * targetX;
                    const startY = inverse ** 3 * y + 3 * inverse ** 2 * t * (y - h * .12) + 3 * inverse * t ** 2 * (targetY + h * .1) + t ** 3 * targetY;
                    const side = (branch + index) % 2 ? -1 : 1;
                    const dx = targetX - x;
                    const dy = targetY - y;
                    const length = Math.hypot(dx, dy) || 1;
                    const reach = Math.min(w, h) * (.07 + ((branch * 7 + index * 3) % 11) * .009);
                    const endX = startX - dy / length * reach * side;
                    const endY = startY + dx / length * reach * side;
                    const forkX = startX + (endX - startX) * .61 + dx / length * reach * .2;
                    const forkY = startY + (endY - startY) * .61 + dy / length * reach * .2;
                    const radius = 3 + (branch * 3 + index * 5) % 13;
                    const branchOnset = onset + span * t;
                    const branchSpan = .035 + branch * .004;
                    const twigX = forkX + dx / length * reach * side * .5;
                    const twigY = forkY + dy / length * reach * side * .5;
                    const primary = backdropTrace(`M${startX} ${startY} Q${startX + dx / length * reach * .35} ${startY + dy / length * reach * .35} ${forkX} ${forkY} T${endX} ${endY}`, trace, .9, branchOnset, branchSpan, current, motion);
                    const secondary = backdropTrace(`M${forkX} ${forkY} Q${forkX - dy / length * reach * .3} ${forkY + dx / length * reach * .3} ${twigX} ${twigY}`, data, .6, branchOnset + branchSpan * .6, branchSpan * .65, current, motion);
                    const terminal = backdropAppearance(circle(endX, endY, radius, data) + circle(endX, endY, radius * .55, trace, .8) + glyph(endX + radius + 8, endY, branch + index, 3), branchOnset + branchSpan, .008, current, motion);
                    const tip = backdropAppearance(circle(twigX, twigY, 2 + branch % 4, bright, .7), branchOnset + branchSpan * 1.25, .008, current, motion);
                    network += `<g opacity=".3">${primary}${secondary}${terminal}${tip}</g>`;
                }
                node.packet.style.offsetPath = `path('${path}')`;
                node.flow?.cancel();
                node.flow = motion && node.packet.animate ? node.packet.animate([
                    { offsetDistance: '0%', opacity: 0 },
                    { offsetDistance: '12%', opacity: 0.6, offset: 0.12 },
                    { offsetDistance: '85%', opacity: 0.6, offset: 0.85 },
                    { offsetDistance: '100%', opacity: 0 }
                ], { duration: 15000 + index * 4300, delay: (onset + span) * duration, iterations: Infinity }) : null;
                if (node.flow) node.flow.currentTime = current * duration;
            });
            const svg = `<svg xmlns="http://www.w3.org/2000/svg" data-timeline="${startedAt}" width="${w}" height="${h}" viewBox="0 0 ${w} ${h}">${network}</svg>`;
            backdrop.style.setProperty('--borg-background-network', `url("data:image/svg+xml,${encodeURIComponent(svg)}")`);
        }

        function growth(value, seed) {
            const times = [0, 0.17, 0.42, 0.73, 1];
            const values = seed % 2 ? [0, 0.03, 0.46, 0.66, 1] : [0, 0.19, 0.31, 0.84, 1];
            const current = clamp(value);
            const index = Math.min(3, times.findIndex((time, i) => current >= time && current <= times[i + 1]));
            return values[index] + (values[index + 1] - values[index]) * (current - times[index]) / (times[index + 1] - times[index]);
        }

        function infection(panel, local, moving, w, h) {
            const { seed } = panel;
            const [, , trace, bright] = tones[panel.family];
            const entry = panel.entry || { x: (seed % 83 + 8) / 100, y: ((seed >>> 7) % 83 + 8) / 100 };
            const count = w > 400 && h > 80 ? 5 : 3;
            const points = [{ x: entry.x * w, y: entry.y * h }];
            for (let i = 1; i < count; i++) points.push({
                x: w * (panel.entries?.[i - 1]?.x ?? (0.08 + ((seed >>> (i * 3)) % 83) / 100)),
                y: h * (panel.entries?.[i - 1]?.y ?? (0.08 + ((seed >>> (i * 4)) % 83) / 100))
            });
            let patches = '';
            let branches = '';
            for (let i = 0; i < count; i++) {
                const point = points[i];
                const parent = points[Math.max(0, i - 1)];
                const delay = i * 0.125;
                const age = (local - delay) / (1 - delay);
                const amount = growth(age, seed + i);
                const radius = Math.hypot(Math.max(point.x, w - point.x), Math.max(point.y, h - point.y)) / 0.7;
                const values = (seed + i) % 2 ? '0;.03;.46;.66;1' : '0;.19;.31;.84;1';
                const animate = (attribute, scale, inverse = false) => moving ? `<animate attributeName="${attribute}" values="${values.split(';').map(value => (inverse ? 1 - Number(value) : Number(value)) * scale).join(';')}" keyTimes="0;.17;.42;.73;1" dur="${(1 - delay) * panel.span * duration / 1000}s" begin="${(delay - local) * panel.span * duration / 1000}s" fill="freeze"/>` : '';
                patches += `<circle cx="${point.x}" cy="${point.y}" r="${radius * amount}" fill="url(#colony)">${animate('r', radius)}</circle>`;
                if (i) {
                    const bend = (seed % 2 ? -1 : 1) * Math.min(45, h * 0.3);
                    const path = `M${parent.x} ${parent.y} C${parent.x + (point.x - parent.x) * 0.2} ${parent.y + bend} ${point.x - (point.x - parent.x) * 0.3} ${point.y - bend} ${point.x} ${point.y}`;
                    branches += `<path d="${path}" pathLength="100" stroke="${trace}" stroke-width="1.2" stroke-dasharray="100" stroke-dashoffset="${100 * (1 - amount)}" fill="none" opacity=".35">${animate('stroke-dashoffset', 100, true)}</path>`;
                }
                const nodeRadius = Math.max(2, Math.min(panel.heading ? 4 : 10, 3 + (seed + i * 3) % 9, h / 5));
                const opacity = 0.4;
                branches += `<g opacity="${amount * opacity}">${animate('opacity', opacity)}<circle cx="${point.x}" cy="${point.y}" r="${nodeRadius}" fill="url(#node)" stroke="${trace}"/><circle cx="${point.x}" cy="${point.y}" r="${nodeRadius * 0.45}" stroke="${bright}" stroke-width=".7" fill="none"/></g>`;
            }
            return {
                definitions: `<radialGradient id="colony"><stop offset=".72" stop-color="white"/><stop offset="1" stop-color="white" stop-opacity="0"/></radialGradient><mask id="growth" maskUnits="userSpaceOnUse" x="0" y="0" width="${w}" height="${h}">${patches}</mask>`,
                branches
            };
        }

        function skin(panel, local, moving, animated) {
            const { width: w, seed, family, heading } = panel;
            const h = panel.height > 1400 ? 64 : panel.height;
            const [surface, plate, trace, bright, data] = tones[family];
            const colors = {
                '#071005': surface, '#0a1408': surface,
                '#21351d': surface, '#3d5631': plate, '#2f572b': plate, '#335b2c': plate,
                '#538c35': trace, '#4b8b38': trace, '#598a45': trace, '#3f7435': trace,
                '#548f42': trace, '#68ae3c': trace, '#38562c': trace, '#547a34': trace,
                '#659646': trace, '#476d32': trace,
                '#a0dc69': bright, '#a3cf69': bright, '#adf27d': bright, '#b9eb7c': bright,
                '#9cca5d': bright, '#96ba62': data, '#9ca96c': data, '#87944e': data, '#94bb60': data
            };
            const edge = Math.min(heading ? 4 : 9, h / 4);
            const inset = 2 + seed % 3;
            const corner = 5 + seed % 7;
            const path = `M ${inset + corner} ${inset} H ${w - inset - corner} L ${w - inset} ${inset + corner} V ${h - inset - corner} L ${w - inset - corner} ${h - inset} H ${inset + corner} L ${inset} ${h - inset - corner} V ${inset + corner} Z`;
            let details = '';
            if (family === 'conduits') {
                for (let rail = 0; rail < 3; rail++) {
                    const offset = 2 + rail * 3;
                    details += line(`M 0 ${h - offset} H ${w * 0.3 - 8} Q ${w * 0.3} ${h - offset} ${w * 0.3 + 5} ${h - offset - 4} H ${w - 8} V 0`, rail === 1 ? '#96ba62' : '#2f572b', rail === 1 ? 1 : 2);
                }
                for (let i = 0; i < 14; i++) {
                    const x = (i * 47 + seed % 43) % w;
                    details += line(`M ${x} 0 v 4 l 5 4 h 10`, '#4b8b38');
                }
            } else if (family === 'neural') {
                for (let i = 0; i < 7; i++) {
                    const x = 12 + (w - 24) * i / 6;
                    const y = i % 2 ? h - 4 : 5;
                    details += circle(x, y, 4 + i % 3, '#598a45', 2) + circle(x, y, 1.5, '#b9eb7c');
                    if (i) details += line(`M ${x - (w - 24) / 6} ${i % 2 ? 5 : h - 4} Q ${x - 12} ${h / 2} ${x} ${y}`, '#3f7435');
                }
            } else if (family === 'armour') {
                for (let i = 0; i < 9; i++) {
                    const x = i * w / 9;
                    const size = w / 9 - 4;
                    const y = i % 2 ? h - 8 : 0;
                    details += `<path d="M ${x} ${y} h ${size} l -5 7 h ${-size + 5} Z" fill="${i % 3 ? '#21351d' : '#3d5631'}"/>`;
                    details += box(x + 3, y + 2, Math.min(13, size - 6), 1, '#9ca96c');
                }
                details += line(path, '#548f42', 1.5);
            } else if (family === 'glyphs') {
                const columns = Math.min(20, Math.floor(w / 19));
                for (let i = 0; i < columns; i++) {
                    const x = 10 + i * (w - 20) / Math.max(1, columns - 1);
                    details += glyph(x, i % 3 ? h - 4 : 4, seed + i, 3);
                    if (i % 3 === 0) details += box(x - 4, 0, 8, 1, '#87944e');
                }
            } else if (family === 'lattice') {
                for (let i = 0; i < 20; i++) {
                    const x = i * w / 20;
                    details += line(`M ${x} 0 l 8 8 h 8 l 8 -8 M ${x} ${h} l 8 -8 h 8 l 8 8`, '#38562c');
                    details += box(x + 8, h - 5, 3 + (seed + i) % 8, 2, i % 4 ? '#547a34' : '#9cca5d');
                }
            } else {
                for (let i = 0; i < 4; i++) {
                    const x = i % 2 ? w - 4 : 4;
                    const y = i < 2 ? 4 : h - 4;
                    for (let r = 3; r <= 13; r += 4) details += circle(x, y, r, r === 7 ? '#94bb60' : '#335b2c');
                    details += line(`M ${x} ${y} H ${i % 2 ? w * 0.6 : w * 0.4}`, '#659646');
                }
            }
            const lane = heading && w > 320 ? `<g>${box(w - 75, 2, 68, Math.max(8, h - 4), '#0a1408')}${[0, 1, 2, 3].map(i => glyph(w - 63 + i * 15, h / 2, seed + i)).join('')}</g>` : '';
            const pulse = animated ? `<path d="${path}" fill="none" stroke="#adf27d" stroke-width="1.4" stroke-dasharray="6 14 2 200" opacity="0"><animate attributeName="opacity" from="0" to=".65" begin="${Math.max(0, (1 - local) * panel.span * duration / 1000)}s" dur="1.1s" fill="freeze"/><animate attributeName="stroke-dashoffset" from="0" to="-222" begin="${Math.max(0, (1 - local) * panel.span * duration / 1000)}s" dur="${5 + seed % 7}s" repeatCount="indefinite"/></path>` : '';
            const signal = seed % 5 === 0 ? box(seed % 2 ? w - 16 : 11, h - 4, 5, 2, '#c39a45') : '';
            const colony = infection(panel, local, moving, w, h);
            const mask = local < 1 ? ' mask="url(#growth)"' : '';
            let network = '';
            if (!heading && h > 45 && w > 60) {
                const count = Math.min(6, Math.max(2, Math.floor(w / 150)));
                for (let i = 0; i < count; i++) {
                    const x = 9 + ((seed % 73 + i * 137) % Math.max(1, w - 25));
                    const y = i % 2 ? h - 13 : 13;
                    const radius = 4 + (seed + i * 7) % 8;
                    const nextX = Math.min(w - 5, x + 45 + seed % 80);
                    network += `<g opacity="${family === 'neural' || family === 'transwarp' ? '.6' : '.3'}">${line(`M ${x - 25} ${y} Q ${x - 12} ${y + 12} ${x} ${y} Q ${x + 16} ${y - 14} ${nextX} ${y}`, trace)}<circle cx="${x}" cy="${y}" r="${radius}" fill="url(#node)"/>${circle(x, y, radius, data)}${circle(x, y, radius * 0.4, bright)}${glyph(nextX, y, seed + i, 3)}</g>`;
                }
            }
            const definitions = `<defs><clipPath id="edge"><path clip-rule="evenodd" d="M0 0H${w}V${h}H0Z M${edge} ${edge}V${Math.max(edge, h - edge)}H${Math.max(edge, w - edge)}V${edge}Z"/></clipPath><pattern id="substrate" width="48" height="32" patternUnits="userSpaceOnUse"><path d="M0 0H48M0 0V32M24 0V8H48M0 24H12V32" fill="none" stroke="${plate}" stroke-width=".5" opacity=".35"/></pattern><radialGradient id="node"><stop stop-color="${bright}" stop-opacity=".6"/><stop offset=".45" stop-color="${trace}" stop-opacity=".35"/><stop offset="1" stop-color="${surface}" stop-opacity=".1"/></radialGradient>${colony.definitions}</defs>`;
            const svg = `<svg xmlns="http://www.w3.org/2000/svg" data-timeline="${startedAt}" width="${w}" height="${h}" viewBox="0 0 ${w} ${h}">${definitions}<g${mask}><rect width="${w}" height="${h}" fill="${surface}"/>${heading ? '' : `<rect width="${w}" height="${h}" fill="url(#substrate)" opacity=".45"/>`}${network}<g clip-path="url(#edge)">${details}${heading ? '' : line(path, '#476d32')}${pulse}${signal}</g>${lane}</g>${colony.branches}</svg>`;
            return svg.replace(/#[0-9a-f]{6}/g, color => colors[color] || color);
        }

        function renderPanel(panel, current, active, animated = false) {
            const { element } = panel;
            const local = clamp((current - panel.onset) / panel.span);
            if (active && nativeColors && local > 0 && local < 1) {
                if (!panel.animation) panel.animation = element.animate([
                    { '--borg-panel-level': '0' }, { '--borg-panel-level': '1' }
                ], { duration: panel.span * duration, delay: panel.onset * duration, fill: 'both' });
                if (Math.abs(Number(panel.animation.currentTime) - current * duration) > 350) panel.animation.currentTime = current * duration;
            } else {
                panel.animation?.cancel();
                panel.animation = null;
                if (element.style.getPropertyValue('--borg-panel-level') !== String(local)) element.style.setProperty('--borg-panel-level', String(local));
            }
            const moving = active && local > 0 && local < 1;
            const phase = local === 0 ? 'waiting' : active ? 'live' : local === 1 ? 'complete' : local.toFixed(5);
            const key = `${startedAt}:${panel.geometry}:${phase}:${animated}`;
            if (panel.key === key || !panel.width || !panel.height) return;
            panel.key = key;
            element.style.setProperty('--borg-skin-size', panel.height > 1400 ? '100% 64px' : '100% 100%');
            if (!local) { element.style.setProperty('--borg-skin', 'none'); return; }
            const source = `data:image/svg+xml,${encodeURIComponent(skin(panel, local, moving, animated))}`;
            const apply = () => { if (panel.key === key && element.isConnected) element.style.setProperty('--borg-skin', `url("${source}")`); };
            const picture = new Image();
            if (!picture.decode) { apply(); return; }
            picture.src = source;
            const pending = picture.decode().catch(() => {}).then(apply).finally(() => pendingSkins.delete(pending));
            pendingSkins.add(pending);
        }

        function renderPanels() {
            if (graphDirty) rebuildGraph();
            const current = progress();
            const motion = !reducedMotion.matches;
            let moving = 0;
            for (const panel of visible) {
                const { element } = panel;
                if (!element.isConnected) { visible.delete(panel); panels.delete(element); continue; }
                const animated = panel.height <= 1400 && motion && moving < 8
                    && (panel.seed % 3 === 0 || panel.family === 'neural' || panel.family === 'transwarp');
                if (animated) moving++;
                renderPanel(panel, current, motion, animated);
            }
            renderConnections(current, motion);
            if (preparing && (visible.size || intersectionReady)) finishPreparation();
        }

        function finishPreparation() {
            Promise.allSettled([...pendingSkins]).then(() => {
                if (pendingSkins.size) { finishPreparation(); return; }
                root.removeAttribute('data-borg-preparing');
                preparing = false;
            });
        }

        function queueRefresh() {
            if (refreshFrame !== null || document.hidden) return;
            refreshFrame = window.requestAnimationFrame(() => { refreshFrame = null; renderPanels(); });
        }

        const intersection = window.IntersectionObserver ? new IntersectionObserver(entries => {
            intersectionReady = true;
            for (const entry of entries) {
                const panel = panels.get(entry.target);
                if (!panel) continue;
                if (entry.isIntersecting) visible.add(panel);
                else {
                    visible.delete(panel);
                    renderPanel(panel, progress(), false);
                }
            }
            queueRefresh();
        }, { rootMargin: '400px' }) : null;
        const sizes = window.ResizeObserver ? new ResizeObserver(entries => {
            for (const entry of entries) {
                const panel = panels.get(entry.target);
                if (!panel) continue;
                const size = entry.borderBoxSize?.[0];
                panel.width = Math.round(size?.inlineSize ?? entry.target.offsetWidth);
                panel.height = Math.round(size?.blockSize ?? entry.target.offsetHeight);
            }
            graphDirty = true;
            queueRefresh();
        }) : null;

        function enroll(element) {
            if (panels.has(element)) return;
            const seed = hash(identity(element));
            const parentPanel = element.matches('th, td.th, .box_title, .divhead, .ttop') ? panels.get(element.parentElement?.closest('.borg-console')) : null;
            const family = parentPanel?.family || families[seed % families.length];
            const [surface, plate, trace, bright] = tones[family];
            rememberBackground(element);
            element.style.setProperty('--borg-surface-tone', surface);
            element.style.setProperty('--borg-panel-tone', plate);
            element.style.setProperty('--borg-edge-tone', trace);
            element.style.setProperty('--borg-text-tone', `color-mix(in srgb, ${bright} 35%, #c4d0b8)`);
            const panel = { element, seed, family, key: '',
                width: element.offsetWidth, height: element.offsetHeight,
                heading: element.matches('th, td.th, .box_title, .divhead, .ttop, .mainbar_box_header, .mainbar_nav_box_header, .sidebar_box_header'),
                shell: element.matches('.sidebar, .header, .nav_box_content, .modal-content, .dropdown-menu'),
                onset: 0.035,
                span: 0.025 + seed % 31 / 1000 + (element.offsetHeight > 180 ? 0.015 : 0) };
            panels.set(element, panel);
            graphDirty = true;
            element.classList.add('borg-console');
            element.dataset.borgVariant = panel.family;
            if (panel.shell) element.dataset.borgShell = 'true';
            sizes?.observe(element);
            if (intersection) intersection.observe(element);
            else visible.add(panel);
        }

        function enrollTree(node) {
            if (node.nodeType !== 1) return;
            if (node.matches(selector)) enroll(node);
            for (const element of node.querySelectorAll(selector)) enroll(element);
        }

        function updateBackground() {
            const current = progress();
            const motion = !reducedMotion.matches && backdrop.animate;
            updateBackdropNetwork(current, motion);
            backgroundNodes.forEach((node, index) => {
                const local = clamp((current - node.onset) / node.span);
                if (node.activation) {
                    if (Math.abs(Number(node.activation.currentTime) - current * duration) > 350) node.activation.currentTime = current * duration;
                    if (motion && node.activation.playState === 'paused') node.activation.play();
                    else if (!motion) node.activation.pause();
                } else node.activity.style.opacity = String(clamp((current - node.onset - node.span) * duration / 1200));
                const phase = local <= 0 ? 'waiting' : local >= 1 ? 'complete' : motion ? 'live' : local.toFixed(4);
                const key = `${startedAt}:${phase}`;
                if (node.artworkKey === key) return;
                node.artworkKey = key;
                if (phase === 'waiting') {
                    node.element.style.visibility = 'hidden';
                    node.layers.forEach(layer => { layer.style.backgroundImage = 'none'; });
                    return;
                }
                const artwork = backdropArtwork(node, index, current, motion && local < 1);
                node.element.style.setProperty('--borg-art-left', `${artwork.bounds.x / 256 * 100}%`);
                node.element.style.setProperty('--borg-art-top', `${artwork.bounds.y / 256 * 100}%`);
                node.element.style.setProperty('--borg-art-width', `${(artwork.bounds.right - artwork.bounds.x) / 256 * 100}%`);
                node.element.style.setProperty('--borg-art-height', `${(artwork.bounds.bottom - artwork.bounds.y) / 256 * 100}%`);
                if (node.idleEpoch !== startedAt) {
                    node.idleEpoch = startedAt;
                    node.idleSignals = backdropArtwork(node, index, 1, false).signals;
                }
                const images = [artwork.frame, artwork.glow, ...node.idleSignals].map(image => `data:image/svg+xml,${encodeURIComponent(image)}`);
                const apply = () => {
                    if (node.artworkKey !== key) return;
                    images.forEach((image, layer) => { node.layers[layer].style.backgroundImage = `url("${image}")`; });
                    node.element.style.visibility = 'visible';
                };
                if (window.Image?.prototype.decode) {
                    const pending = Promise.allSettled(images.map(source => {
                        const image = new Image();
                        image.src = source;
                        return image.decode();
                    })).then(apply).finally(() => pendingSkins.delete(pending));
                    pendingSkins.add(pending);
                } else apply();
            });
            const key = current >= 1 ? 'complete' : motion ? `animated:${startedAt}` : current.toFixed(3);
            if (key !== backgroundKey) {
                backgroundKey = key;
                backgroundAnimation?.cancel();
                backgroundAnimation = null;
                if (motion && current < 1) backgroundAnimation = backdrop.animate([
                    { opacity: 0, offset: 0 },
                    { opacity: 0.03, offset: 0.17 },
                    { opacity: 0.46, offset: 0.42 },
                    { opacity: 0.66, offset: 0.73 },
                    { opacity: 1, offset: 1 }
                ], { duration, fill: 'both' });
                else {
                    backdrop.style.opacity = String(growth(current, 37));
                }
            }
            if (backgroundAnimation && Math.abs(Number(backgroundAnimation.currentTime) - current * duration) > 350) backgroundAnimation.currentTime = current * duration;
        }

        function tick() {
            if (timer !== null) window.clearTimeout(timer);
            timer = null;
            if (document.hidden) return;
            if (!dragging) { updateTheme(); updateBackground(); renderPanels(); }
            save();
            if (progress() < 1 || dragging) timer = window.setTimeout(tick, 250);
        }

        function updateMotion() {
            root.dataset.borgMotion = reducedMotion.matches ? 'still' : 'animated';
            updateBackground();
            renderPanels();
        }
        reducedMotion.addEventListener('change', updateMotion);

        const mutations = new MutationObserver(records => {
            let changed = false;
            let removed = false;
            for (const record of records) {
                if (backdrop.contains(record.target)) continue;
                for (const node of record.addedNodes) {
                    if (node.nodeType === 1) { enrollTree(node); changed = true; }
                }
                if (Array.from(record.removedNodes).some(node => node.nodeType === 1)) {
                    removed = true;
                    changed = true;
                }
            }
            if (removed) {
                for (const [element, panel] of panels) {
                    if (element.isConnected) continue;
                    visible.delete(panel);
                    panels.delete(element);
                    panel.animation?.cancel();
                    sizes?.unobserve(element);
                    intersection?.unobserve(element);
                }
            }
            if (changed) { graphDirty = true; queueRefresh(); }
        });
        initializeBackdrop();
        mutations.observe(document.body, { childList: true, subtree: true });
        document.addEventListener('mousedown', event => {
            if (event.target.closest?.('#popup-drag')) dragging = true;
        }, true);
        document.addEventListener('mouseup', () => { if (dragging) { dragging = false; tick(); } }, true);
        window.addEventListener('blur', () => { if (dragging) { dragging = false; tick(); } });
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) { save(); window.clearTimeout(timer); timer = null; }
            else {
                dragging = false;
                for (const panel of visible) panel.key = '';
                backgroundKey = '';
                tick();
            }
        });
        window.addEventListener('pageshow', tick);
        window.addEventListener('resize', () => { graphDirty = true; queueRefresh(); });
        enrollTree(document.body);
        initialized = true;
        updateTheme();
        updateMotion();
        tick();
        if (!panels.size) finishPreparation();
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize, { once: true });
    else initialize();
})();
