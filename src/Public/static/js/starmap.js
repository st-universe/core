function getEventMap (Ereignis)
{
	if (!Ereignis) Ereignis = window.event;
	browser = navigator.appName;
	if(browser == "Microsoft Internet Explorer" || browser == "Opera")
	{
		showEntries(Ereignis.offsetX,Ereignis.offsetY);
	}
	else
	{
		showEntries(Ereignis.layerX,Ereignis.layerY);
	}
}
function showEntries(x,y)
{
	elt = 'history';
	openPJsWin(elt);
	sendRequest('backend/common/history.php?x=' + x + '&y=' + y);
}