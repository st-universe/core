function getCommodityLocations(commodityId) {
	elt = 'commodityLocations';
	openPJsWin(elt, 1);
	ajax_update(elt, 'database.php?commodityId=' + commodityId + '&SHOW_COMMODITIES_LOCATIONS=1');
}
function showColonySurface(id) {
	elt = 'colonysurface';
	openPJsWin(elt, 1);
	ajax_update(elt, 'database.php?SHOW_SURFACE=1&id=' + id);
}
