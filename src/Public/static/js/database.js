function getCommodityLocations(commodityId) {
	elt = 'commodityLocations';
	openPJsWin(elt, 1);
	ajax_update(elt,'database.php?commodityId='+commodityId+'&SHOW_COMMODITIES_LOCATIONS=1');
}
