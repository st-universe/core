import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';
import { test } from 'node:test';

const source = fs.readFileSync(new URL('../../src/Public/static/js/comm.js', import.meta.url), 'utf8');

function loadActions() {
    const requests = [];
    const fields = {
        delete_single: { value: 0 },
        formaction: { name: '' },
        catname: { value: 'Neuer Ordner' },
        deletion_mark: { value: '' }
    };
    const state = { token: 'first-token', submissions: 0 };
    const context = vm.createContext({
        document: {
            addEventListener() {},
            querySelector: () => ({ dataset: { pmCategoryToken: state.token } }),
            getElementById: () => ({ elements: { 'deleted[]': [] } }),
            forms: {
                pmlist: { submit: () => state.submissions++ },
                editcat: { elements: { pmcat: { value: 8 }, sstr: { value: 'popup-token' } } }
            }
        },
        $: (id) => fields[id],
        hidePopup() {},
        Ajax: { Updater: function (target, url, options) { requests.push({ target, url, options }); } },
        nodelistToString: () => '7,8'
    });
    vm.runInContext(source, context);
    return { context, requests, fields, state };
}

test('single and marked message deletion submit the existing POST form', () => {
    const { context, fields, state } = loadActions();
    context.deleteSinglePm(7);
    assert.equal(fields.delete_single.value, 7);
    assert.equal(fields.formaction.name, 'B_DELETE_PMS');
    context.deleteMarkedPMs();
    assert.equal(fields.deletion_mark.value, '7,8');
    assert.equal(state.submissions, 2);
});

test('renaming a folder sends its popup token by POST and refreshes the list', () => {
    const { context, requests } = loadActions();
    context.changeCategoryName();
    assert.equal(requests.length, 1);
    assert.equal(requests[0].target, 'catlist');
    assert.equal(requests[0].url, '/pm.php');
    assert.equal(requests[0].options.method, 'post');
    assert.equal(requests[0].options.evalScripts, true);
    assert.deepEqual({ ...requests[0].options.parameters }, {
        B_EDIT_PMCATEGORY_NAME: 1, pmcat: 8, catname: 'Neuer Ordner', sstr: 'popup-token'
    });
});

test('repeated folder sorting uses the refreshed one-use token', () => {
    const { context, requests, state } = loadActions();
    context.pmCategoryList = { toArray: () => [8, 7] };
    context.updateCategoryOrder();
    state.token = 'renewed-token';
    context.updateCategoryOrder();
    for (const [index, request] of requests.entries()) {
        assert.equal(request.target, 'catlist');
        assert.equal(request.options.method, 'post');
        assert.equal(request.options.evalScripts, true);
        assert.deepEqual({ ...request.options.parameters }, {
            B_PMCATEGORY_SORT: 1, catlist: '8,7', sstr: index === 0 ? 'first-token' : 'renewed-token'
        });
    }
});
