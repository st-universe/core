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
	link = Element.select(obj, 'a');
	if (link.length == 0) {
		return;
	}
	goToUrl(link[0].href);
}
function shipSelectorChoose(obj) {
	shiplist = document.getElementById("shiplist");
	if (!shiplist) {
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

function decreaseShuttleAmount(cid) {
	old = parseInt($('shuttleAmount_' + cid).value);

	if (old > 0) {
		$('shuttleAmount_' + cid).value = old - 1;

		current = parseInt($('storedshuttles').innerHTML);
		$('storedshuttles').innerHTML = current - 1;
	}
}

function increaseShuttleAmount(cid, maxOf, maxTotal) {
	old = parseInt($('shuttleAmount_' + cid).value);
	current = parseInt($('storedshuttles').innerHTML);

	if (old < maxOf && current < maxTotal) {
		$('shuttleAmount_' + cid).value = old + 1;
		$('storedshuttles').innerHTML = current + 1;
	}
}
