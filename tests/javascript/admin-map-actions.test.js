import assert from "node:assert/strict";
import fs from "node:fs";
import vm from "node:vm";
import { test } from "node:test";

const source = fs.readFileSync(new URL("../../src/Public/static/js/mapEditor.js", import.meta.url), "utf8");

function loadEditor({ tokenAvailable = true, tokenRequestFails = false } = {}) {
	const requests = [];
	const listeners = new Map();
	let tokenNumber = 0;
	let submitted = 0;
	const context = vm.createContext({
		URL, URLSearchParams,
		assetUrl: url => url,
		gfx_path: "/assets",
		closeAjaxWindow() {},
		window: { location: { origin: "https://local.test" } },
		document: {
			addEventListener: (event, callback) => listeners.set(event, callback),
			getElementById: () => ({ requestSubmit: () => submitted++ }),
			querySelector: () => null,
			location: {}
		},
		DOMParser: class {
			parseFromString(token) {
				return { querySelector: () => tokenAvailable ? { dataset: { adminMapToken: token } } : null };
			}
		},
		fetch: async (url, options = {}) => {
			requests.push({ url, options });
			if (url === "/admin/?NOOP=1") {
				return { ok: !tokenRequestFails, text: async () => "token-" + (++tokenNumber) };
			}
			return { ok: true };
		},
		$: () => ({ checked: true, style: {} }),
		alert: message => assert.fail(message)
	});
	vm.runInContext(source, context);
	return { context, requests, listeners, submitted: () => submitted };
}

test("map updates send action and repeated fields in POST with a fresh user token each time", async () => {
	const { context, requests } = loadEditor();
	const fields = new URLSearchParams([["fields[]", "1"], ["fields[]", "2"], ["operation", "region"], ["value", "0"]]);
	await context.adminMapPost("/admin/?B_EDIT_FULL_MAP_FIELD=1", fields);
	await context.adminMapPost("/admin/?B_SET_ION_STORM_MOVEMENT=1", new URLSearchParams({ rootId: "7", velocity: "2" }));
	assert.equal(requests.length, 4);
	assert.equal(requests[0].options.cache, "no-store");
	assert.equal(requests[2].options.cache, "no-store");
	for (const [index, request] of [requests[1], requests[3]].entries()) {
		assert.equal(request.url, "https://local.test/admin/");
		assert.equal(request.options.method, "POST");
		assert.equal(request.options.credentials, "same-origin");
		assert.equal(new URLSearchParams(request.options.body).get("sstr"), "token-" + (index + 1));
	}
	const full = new URLSearchParams(requests[1].options.body);
	assert.equal(full.get("B_EDIT_FULL_MAP_FIELD"), "1");
	assert.deepEqual(full.getAll("fields[]"), ["1", "2"]);
	assert.equal(full.get("operation"), "region");
	assert.equal(new URLSearchParams(requests[3].options.body).get("rootId"), "7");
});

for (const options of [{ tokenAvailable: false }, { tokenRequestFails: true }]) {
	test(`map writes stop when a fresh token cannot be obtained ${JSON.stringify(options)}`, async () => {
		const { context, requests } = loadEditor(options);
		await assert.rejects(context.adminMapPost("/admin/?B_EDIT_FIELD=1&field=1&type=2"));
		assert.equal(requests.length, 1);
	});
}

test("legacy map tools route every selected operation through the protected POST helper", () => {
	const { context } = loadEditor();
	const urls = [];
	context.adminMapUpdate = url => urls.push(url);
	for (const name of ["selectedFieldType", "selectedSystemType", "selectedRegion", "areaid", "adminregionselector", "passableselector", "borderselector"]) {
		context[name] = 2;
	}
	context.selectedEffects.add("DAMAGE");
	const field = { style: {}, parentNode: { setAttribute() {}, getAttribute: () => "" }, querySelector: () => null };
	context.updateField(field, 1);
	context.selectNewSystemMapField(1, 1, 1, 2, 2);
	const actions = urls.map(url => [...new URL(url, "https://local.test").searchParams.keys()][0]);
	assert.deepEqual(actions, ["B_EDIT_FIELD", "B_EDIT_SYSTEMTYPE_FIELD", "B_EDIT_REGION", "B_EDIT_INFLUENCE_AREA", "B_EDIT_ADMIN_REGION", "B_EDIT_PASSABLE", "B_EDIT_BORDER", "B_EDIT_EFFECTS", "B_RESET_EFFECTS", "B_EDIT_SYSTEM_FIELD"]);
});

test("regeneration keyboard shortcut submits the protected POST form", () => {
	const { context, listeners, submitted } = loadEditor();
	context.registerSystemEditorNavKeys(1, 2, 3);
	listeners.get("keydown")({ key: "ArrowUp" });
	assert.equal(submitted(), 1);
	assert.equal(context.document.location.href, undefined);
});
