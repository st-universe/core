function openNewCatWindow() {
	elt = 'newcatwin';
	openPJsWin(elt,300);
	ajaxcall(elt,"comm.php?SHOW_NEW_CAT=1");
}

function addNewCategory() {
	name = $('catname').value;
	if (name.length<1) {
		alert('Es wurde kein Name eingegeben');
		return;
	}
	ajaxcall('catlist','comm.php?B_ADD_PMCATEGORY=1&'+Form.Element.serialize('catname'));
	cClick();
}

function changeCategoryName() {
	name = $('catname').value;
	if (name.length<1) {
		alert('Es wurde kein Name eingegeben');
		return;
	}
	catid = document.forms['editcat'].elements['pmcat'].value;
	ajaxcall('catlist','comm.php?B_EDIT_PMCATEGORY_NAME=1&pmcat='+catid+'&'+Form.Element.serialize('catname'));
	cClick();
}

function deleteAllMarkedPMs() {
	deleteMarkedPMs();
}

function deleteMarkedPMs() {
	$('formaction').name = 'B_DELETE_PMS';
	document.forms.pmlist.submit();
}

function markAllPMs() {
	for(var i=0;i<document.pmlist.length;++i) {
		document.forms.pmlist.elements[i].checked = true;
	}
}

function unMarkAllPMs() {
	for(var i=0;i<document.pmlist.length;++i) {
		document.forms.pmlist.elements[i].checked = false;
	}
}

function deleteMarkedContacts() {
	$('formaction').name = 'B_DELETE_CONTACTS';
	document.forms.contactlist.submit();
}

function deleteMarkedIgnores() {
	$('formaction').name = 'B_DELETE_IGNORES';
	document.forms.contactlist.submit();
}

function markAllContacts() {
	for(var i=0;i<document.contactlist.length;++i) {
		document.forms.contactlist.elements[i].checked = true;
	}
}

function unMarkAllContacts() {
	for(var i=0;i<document.contactlist.length;++i) {
		document.forms.contactlist.elements[i].checked = false;
	}
}

function showPMCategoryWindow(catid) {
	elt = 'cateditwin';
	openWindow(elt,1,300);
	ajax_update(elt,"comm.php?SHOW_EDIT_CAT=1&pmcat="+catid);
}

function updateRecipient() {
	var number=document.newpm.recid.selectedIndex;
	if (number<=0 || number>=document.newpm.recid.options.length) {
		document.newpm.recipient.value="";
	} else {
		var Text=document.newpm.recid.options[number].value;
		document.newpm.recipient.value=Text;
	}
}

function switchToPlotSelector() {
	$('writekntitle').className = 'nselected';
	$('writeknplot').className = 'selected';
	$('writekntitleinput').hide();
	$('writeknplotselect').show();
}

function switchToTitleInput() {
	$('writekntitle').className = 'selected';
	$('writeknplot').className = 'nselected';
	$('writekntitleinput').show();
	$('writeknplotselect').hide();
}
function showKnComments(postingId) {
	elt = 'kncomments';
	openWindow(elt,1,450);
	ajax_update(elt,"comm.php?SHOW_KN_COMMENTS=1&posting="+postingId);
}
function postComment(postingId) {
	comment = Form.Element.serialize('comment');
	ajaxcall('kncomments',"comm.php?B_POST_COMMENT=1&posting="+postingId+"&"+comment);
}
function deletePostingComment(commentId) {
	ajaxcall('kncomments',"comm.php?B_DELETE_COMMENT=1&comment="+commentId);
}
function updateCategoryOrder() {
	ajaxrequest('comm.php?B_PMCATEGORY_SORT=1&'+Sortable.serialize('catlist'));
}
function movePm(pmId) {
	$('move_pm').value = pmId;
	$('formaction').name = 'B_MOVE_PM';
	document.forms.pmlist.submit();
}
function saveContactComment(contactId) {
	$('edit_contact').value = contactId;
	$('formaction').name = 'B_EDIT_CONTACT_COMMENT';
	document.forms.contactlist.submit();
}
function emptyContactComment(contactId) {
	$('contact_comment_input_'+contactId).value='';
	saveContactComment(contactId)
}
