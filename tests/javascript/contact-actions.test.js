import assert from "node:assert/strict";
import fs from "node:fs";
import vm from "node:vm";
import { test } from "node:test";

const source = fs.readFileSync(new URL("../../src/Public/static/js/default.js", import.meta.url), "utf8");

function loadActions(selectorPresent = true) {
	const requests = [];
	const context = vm.createContext({
		window: {},
		document: {
			addEventListener() {},
			forms: [{ elements: { selector_contact: { value: "2" } } }]
		},
		$: () => selectorPresent ? { value: "1" } : null,
		Ajax: { Updater: function (target, url, options) { requests.push({ target, url, options }); } }
	});
	vm.runInContext(source, context);
	vm.runInContext('hidePopup = () => {};', context);
	return { context, requests };
}

for (const selectorPresent of [true, false]) {
	test(`adding a contact sends the chosen relationship and token by POST (selector present: ${selectorPresent})`, () => {
		const { context, requests } = loadActions(selectorPresent);
		context.addUserContact("contact", 102, "own-token");
		assert.equal(requests.length, 1);
		const request = requests[0];
		assert.equal(request.target, "contact");
		assert.equal(request.url, "/pm.php");
		assert.equal(request.options.method, "post");
		assert.deepEqual({ ...request.options.parameters }, {
			B_ADD_CONTACT: 1, recid: 102, clmode: selectorPresent ? "1" : "2", cldiv: "contact", sstr: "own-token"
		});
	});
}

test("ignoring a player sends the target and token by POST", () => {
	const { context, requests } = loadActions();
	context.ignoreUser("ignore", 102, "ignore-token");
	assert.equal(requests.length, 1);
	assert.equal(requests[0].target, "ignore");
	assert.equal(requests[0].url, "/pm.php");
	assert.equal(requests[0].options.method, "post");
	assert.deepEqual({ ...requests[0].options.parameters }, { B_IGNORE_USER: 1, SHOW_IGNORE: 1, recid: 102, sstr: "ignore-token" });
});

test("changing contact mode uses the current popup token on every request", () => {
	const { context, requests } = loadActions();
	context.clmodeswitchdiv = "contact";
	context.switchContactMode(7, 2, "first-token");
	context.switchContactMode(7, 1, "renewed-token");
	assert.equal(requests.length, 2);
	for (const [index, request] of requests.entries()) {
		assert.equal(request.target, "contact");
		assert.equal(request.url, "/pm.php");
		assert.equal(request.options.method, "post");
		assert.deepEqual({ ...request.options.parameters }, {
			B_CHANGE_CONTACTMODE: 1, cid: 7, clmode: index === 0 ? 2 : 1, cldiv: "contact", sstr: index === 0 ? "first-token" : "renewed-token"
		});
	}
});
