import assert from "node:assert/strict";
import fs from "node:fs";
import vm from "node:vm";
import { test } from "node:test";

const source = fs.readFileSync(new URL("../../src/Public/static/js/starmap.js", import.meta.url), "utf8");

function navigation(module = "starmap.php", view = "REFRESH_SECTION", macro = "html/map/starmapSectionTable.twig", redirect = false) {
	const listeners = new Map();
	const requests = [];
	const context = vm.createContext({
		document: { addEventListener: (name, handler) => listeners.set(name, handler), location: { href: "" } },
		$: () => null,
		ajax_update: (target, href) => requests.push({ target, href })
	});
	vm.runInContext(source, context);
	context.registerNavKeys(module, view, macro, redirect);
	context.updateSectionAndLayer(42, 2);
	context.updateNavButtons(true, true, true, true);
	return { context, listeners, requests };
}

for (const [key, direction] of [["ArrowLeft", 1], ["ArrowDown", 2], ["ArrowRight", 3], ["ArrowUp", 4]]) {
	test(`${key} refreshes the selected map section with the standard map template`, () => {
		const { listeners, requests } = navigation();
		listeners.get("keydown")({ key });
		assert.equal(requests.length, 1);
		assert.equal(requests[0].target, "starmapsectiontable");
		const url = new URL(requests[0].href, "https://local.test/");
		assert.equal(url.pathname, "/starmap.php");
		assert.deepEqual(Object.fromEntries(url.searchParams), { REFRESH_SECTION: "1", section: "42", layerid: "2", direction: String(direction), macro: "html/map/starmapSectionTable.twig" });
	});
}

test("the administrator map editor keeps full-page navigation", () => {
	const { context, listeners, requests } = navigation("/admin/", "SHOW_EDIT_MAP_SECTION", "", true);
	listeners.get("keydown")({ key: "ArrowRight" });
	assert.equal(requests.length, 0);
	const url = new URL(context.document.location.href, "https://local.test/");
	assert.equal(url.pathname, "/admin/");
	assert.deepEqual(Object.fromEntries(url.searchParams), { SHOW_EDIT_MAP_SECTION: "1", section: "42", layerid: "2", direction: "3", macro: "" });
});

test("disabled map directions remain disabled", () => {
	const { context, listeners, requests } = navigation();
	context.updateNavButtons(false, false, false, false);
	for (const key of ["ArrowLeft", "ArrowDown", "ArrowRight", "ArrowUp"]) {
		listeners.get("keydown")({ key });
	}
	assert.equal(requests.length, 0);
});

test("the colony telescope keeps its dedicated map template", () => {
	const { listeners, requests } = navigation("starmap.php", "REFRESH_SECTION", "html/colony/telescopeSectionTable.twig");
	listeners.get("keydown")({ key: "ArrowRight" });
	assert.equal(requests.length, 1);
	const url = new URL(requests[0].href, "https://local.test/");
	assert.equal(url.searchParams.get("macro"), "html/colony/telescopeSectionTable.twig");
	assert.equal(url.searchParams.get("direction"), "3");
});
