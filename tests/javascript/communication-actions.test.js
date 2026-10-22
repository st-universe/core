import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';
import { test } from 'node:test';

const source = fs.readFileSync(new URL('../../src/Public/static/js/comm.js', import.meta.url), 'utf8');

function loadActions() {
    const requests = [];
    const fields = {
        postCommentButton: { disabled: false },
        comment: { value: 'Mein Kommentar' },
        kn_comment_session_string: { value: 'first-token' }
    };
    const context = vm.createContext({
        document: { addEventListener() {} },
        $: (id) => fields[id],
        Ajax: { Updater: function (target, url, options) { requests.push({ target, url, options }); } }
    });
    vm.runInContext(source, context);
    return { context, requests, fields };
}

test('posting a comment sends text and token by POST and prevents duplicate submission', () => {
    const { context, requests, fields } = loadActions();
    context.postComment(7);
    context.postComment(7);
    assert.equal(requests.length, 1);
    assert.equal(fields.postCommentButton.disabled, true);
    assert.equal(requests[0].target, 'kncomments');
    assert.equal(requests[0].url, 'communication.php');
    assert.equal(requests[0].options.method, 'post');
    assert.equal(requests[0].options.evalScripts, true);
    assert.deepEqual({ ...requests[0].options.parameters }, {
        B_POST_COMMENT: 1, knid: 7, comment: 'Mein Kommentar', sstr: 'first-token'
    });
});

test('deleting comments reads the renewed popup token for each subsequent action', () => {
    const { context, requests, fields } = loadActions();
    context.deletePostingComment(7, 8);
    fields.kn_comment_session_string.value = 'renewed-token';
    context.deletePostingComment(7, 9);
    assert.equal(requests.length, 2);
    for (const [index, request] of requests.entries()) {
        assert.equal(request.target, 'kncomments');
        assert.equal(request.url, 'communication.php');
        assert.equal(request.options.method, 'post');
        assert.equal(request.options.evalScripts, true);
        assert.deepEqual({ ...request.options.parameters }, {
            B_DELETE_COMMENT: 1, knid: 7, commentid: index === 0 ? 8 : 9,
            sstr: index === 0 ? 'first-token' : 'renewed-token'
        });
    }
});
