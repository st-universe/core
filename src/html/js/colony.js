function buildMenuScrollUp(menu,offset) {
	if (offset - scrollOffset < 0) {
		var newOffset = 0;
	} else {
		var newOffset = offset-scrollOffset;
	}
	buildMenuScroll(menu,newOffset);
}

function buildMenuScrollDown(menu,offset) {
	var newOffset = offset+scrollOffset;
	buildMenuScroll(menu,newOffset);
}

function buildMenuScroll(menu,offset) {
	ajax_update('buildmenu'+menu,'colony.php?id='+colonyid+'&B_SCROLL_BUILDMENU=1&menu='+menu+'&offset='+offset);
}

function switchColonyMenu(menu,func) {
	closeAjaxWindow();
	url = 'colony.php?id='+colonyid+'&B_SWITCH_COLONYMENU=1&menu='+menu;
	if (func) {
		url = url+'&func='+func;
	}
	new Ajax.Updater('colonymenu',url,{
		onComplete: function(transport) {
			initBuildmenuMouseEvent();
		},
		method: 'get'
	}
	);
}

var selectedbuilding = 0;

function openBuildingInfo(buildingId) {
	closeAjaxWindow();
	elt = 'buildinginfo';
	openPJsWin(elt);
	ajax_update(elt,'colony.php?id='+colonyid+'&SHOW_BUILDING=1&bid='+buildingId);
	ajax_update('colsurface','colony.php?id='+colonyid+'&SHOW_COLONY_SURFACE=1&bid='+buildingId);
	buildmode = 1;
	selectedbuilding = buildingId;
}

function closeBuildingInfo() {
	ajax_update('colsurface','colony.php?id='+colonyid+'&SHOW_COLONY_SURFACE=1');
	buildmode = 0;
	selectedbuilding = 0;
}

var oldimg = 0;
var fieldonm = 0;

function showFieldInfo(field) {
	if (field == 0) {
		return;
	}
	if (field != fieldonm) {
		fieldonm = 0;
		return;
	}
	elt = 'fieldinfo';
	openPJsWin(elt);
	ajax_update(elt,'colony.php?id='+colonyid+'&SHOW_FIELD_INFO=1&fid='+field);
}
function fieldMouseOver(obj,field,building,fieldtype) {
	document.body.style.cursor = 'pointer';
	if (buildmode == 1 && building == 0) {
		if (obj.parentNode.parentNode.parentNode.parentNode.className == 'cfb') {
			oldimg = obj.src;
			obj.parentNode.style.backgroundImage = 'url('+oldimg+')';
			obj.src = gfx_path+"/buildings/"+selectedbuilding+"/0at.png";
		}
	}
	if (buildmode == 1 && $('building_preview_'+fieldtype)) {
		$('building_preview_default').style.display = 'none';
		$('building_preview_'+fieldtype).style.display = 'block';
	}
}

function fieldMouseOut(obj,fieldtype) {
	if (buildmode == 1 && oldimg != 0) {
		if (obj.parentNode.parentNode.parentNode.parentNode.className == 'cfb') {
			obj.src = oldimg;
			obj.parentNode.style.backgroundImage = '';
			oldimg = 0;
		}
	}
	if (buildmode == 1 && $('building_preview_'+fieldtype)) {
		$('building_preview_'+fieldtype).style.display = 'none';
		$('building_preview_default').style.display = 'block';
	}
	document.body.style.cursor = 'auto';
}

function fieldMouseClick(obj,field,buildingId) {
	if (buildmode == 1) {
		if (obj.parentNode.className=='cfb') {
			if (buildingId > 0) {
				if (confirm('Soll das Gebäude auf diesem Feld abgerissen werden?')) {
					buildOnField(field);
				}
			} else {
				buildOnField(field);
			}
		}
	} else {
		fieldAction(field);
	}
}

function fieldAction(field) {
	fieldonm = 0;
	elt = 'fieldaction';
	openPJsWin(elt,1);
	ajax_update(elt,'colony.php?id='+colonyid+'&SHOW_FIELD=1&fid='+field);
}
function buildOnField(field) {
    new Ajax.Updater('result','colony.php',{
        method: 'post',
        parameters: 'id='+colonyid+'&B_BUILD=1&fid='+field+'&bid='+selectedbuilding,
        evalScripts: true,
        onComplete: function(transport) {
            $('result').show();
        }
    });
}

function refreshColony() {
	ajax_update('colsurface','colony.php?id='+colonyid+'&SHOW_COLONY_SURFACE=1&bid='+selectedbuilding);
	ajax_update('colonyeps','colony.php?id='+colonyid+'&SHOW_EPSBAR_AJAX=1');
	ajax_update('colonystorage','colony.php?id='+colonyid+'&SHOW_STORAGE_AJAX=1');
	ajax_update('colonylist_navlet','maindesk.php?SHOW_COLONYLIST_AJAX=1');
}

function getOrbitShipList() {
	elt = 'shiplist';
	openPJsWin(elt);
	ajax_update(elt,'colony.php?id='+colonyid+'&SHOW_ORBIT_SHIPLIST=1');
}
var selbg = '';
function shipSelectorHover(obj) {
	selbg = obj.style.backgroundColor;
	obj.style.backgroundColor = '#7c85a8';
	obj.style.cursor = 'pointer';
}
function shipSelectorHoverEnd(obj) {
	obj.style.backgroundColor = selbg;
	obj.style.cursor = 'auto';
	selbg = '';
}
function switchToShip(obj) {
	link = Element.select(obj,'a');
	if (link.length == 0) {
		return;
	}
	goToUrl(link[0].href);
}
function shipSelectorChoose(obj,owner) {
	if (!$('shiplist')) {
		switchToShip(obj);
		return;
	}
	shipSelectorHoverEnd(obj);
	sel = $('shipselector');
	sel.innerHTML = '';
	sel.appendChild(obj.parentNode);
	closeAjaxWindow();
	Element.remove($('shiplist'));
}

function showBToWindow() {
	var shipid = $('selshipid').value;
	elt = 'beam'
	openPJsWin(elt,1);
	ajax_update(elt,'colony.php?id='+colonyid+'&SHOW_BEAMTO=1&target='+shipid);
}

function showBFromWindow() {
	var shipid = $('selshipid').value;
	elt = 'beam'
	openPJsWin(elt,1);
	ajax_update(elt,'colony.php?id='+colonyid+'&SHOW_BEAMFROM=1&target='+shipid);
}

function triggerBeamTo() {
	var shipid = $('selshipid').value;
	ajax_update(elt,'colony.php?id='+colonyid+'&SHOW_BEAMTO=1&target='+shipid);
}

function triggerBeamFrom() {
	var shipid = $('selshipid').value;
	ajax_update(elt,'colony.php?id='+colonyid+'&SHOW_BEAMFROM=1&target='+shipid);
}

function initBuildmenuMouseEvent() {
	onmousewheel($('buildmenu1'),function(delta) {
			scrollBuildmenuByMouse(1,delta);
			}); 
	onmousewheel($('buildmenu2'),function(delta) {
			scrollBuildmenuByMouse(2,delta);
			}); 
	onmousewheel($('buildmenu3'),function(delta) {
			scrollBuildmenuByMouse(3,delta);
			}); 
}
function scrollBuildmenuByMouse(menu,delta) {
	offset = parseInt($('buildmenu'+menu+'_offset').value);
	if (delta < 0) {
		buildMenuScrollDown(menu,offset);		
	}
	if (delta > 0) {
		buildMenuScrollUp(menu,offset);		
	}
}
currentTab = false;
function showModuleSelectTab(obj,tabId) {
	$('module_select_tabs').select('td').each(function(tab) {
		Element.removeClassName(tab,'module_select_base_selected');
	});
	Element.addClassName(obj,'module_select_base_selected');
	if (!currentTab) {
		$('module_select_tab_0').hide();
	} else {
		currentTab.hide();
	}
	$('module_select_tab_'+tabId).show();
	currentTab = $('module_select_tab_'+tabId);
}
function replaceTabImage(type,moduleId,goodId,module_level) {
	if (moduleId == 0) {
		$('tab_image_mod_'+type).src = 'assets/buttons/modul_'+type+'.gif';
		$('module_type_'+type).innerHTML = '';
		updateCrewCount(type,0);
	} else {
		Element.removeClassName($('module_tab_'+type),'module_select_base_mandatory');
		$('tab_image_mod_'+type).src = 'assets/goods/'+goodId+'.gif';
		$('module_type_'+type).innerHTML = $(moduleId+'_content').innerHTML;
		$('module_type_'+type).show();
		updateCrewCount(type,module_level);
	}
	new Effect.Highlight($('module_select_base'));
	enableShipBuildButton();
}
function toggleSpecialModuleDisplay(type,module_id,good_id,module_level) {
	if (Element.select($('module_type_'+type),'.module_special_'+module_id).length == 0) {
		$('module_type_'+type).innerHTML = $(module_id+'_content').innerHTML;
		$('module_type_'+type).show();
	} else {
		Element.select($('module_type_'+type),'.module_special_'+module_id).each(function(elem) {
			if (elem.style.display == 'none') {
				elem.show();
			} else {
				elem.hide();
			}
		});
	}
	new Effect.Highlight($('module_select_base'));
}
var crew_type = new Hash();
function updateCrewCount(type,module_level) {
	crew_type.set(type,module_level);
}
function checkCrewCount() {
	crewcount = 100;
	crew_type.each(function(pair) {
		if (pair.value >= 0) {
			if (pair.value == 0) {
				crewcount -= 10;
			} else {
				if (pair.value > $('module_level_'+pair.key).value) {
					crewcount += 20;
				} else if (pair.value < $('module_level_'+pair.key).value) {
					crewcount -= 10;
				}
			}
		}
	});
	$('crewdisplay').select('div').each(function(elem) {
		elem.hide();
	});
	if (crewcount > 120) {
		Form.Element.disable('buildbutton');
		$('crewerr').show();
		return false;
	}
	if (crewcount <= 120 && crewcount > 110) {
		$('crew120p').show();
		return true;
	}
	if (crewcount <= 110 && crewcount > 100) {
		$('crew110p').show();
		return true;
	}
	$('crew100p').show();
	return true;
}
function enableShipBuildButton() {
	if (!checkCrewCount()) {
		return;
	}
	mandatory = false;
	$('module_select_tabs').select('td').each(function(tab) {
		if (Element.hasClassName(tab,'module_select_base_mandatory')) {
			mandatory = true;
		}
	});
	if (mandatory) {
		return;
	}
	Form.Element.enable('buildbutton');
	new Effect.Highlight($('buildbutton'));
}
function deleteBuildplan(planid,function_id) {
	ajaxcall('colonymenu','colony.php?B_DEL_BUILDPLAN=1&id='+colonyid+'&planid='+planid+'&func='+function_id);
}
function cancelModuleQueueEntries(module_id) {
	ajaxPostUpdate('module_'+module_id+'_action','colony.php','B_CANCEL_MODULECREATION=1&id='+colonyid+'&module='+module_id+'&func='+$('func').value+'&count='+$('module_'+module_id+'_count').value);
	setTimeout('refreshColony()',250);
}
