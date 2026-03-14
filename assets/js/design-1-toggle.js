(function () {
	var c = document.querySelector('.telkari-design-1');
	if (!c) return;
	var btn = c.querySelector('.telkari-trigger');
	if (!btn) return;
	btn.addEventListener('click', function () {
		c.classList.toggle('telkari-open');
	});
	document.addEventListener('click', function (e) {
		if (!c.contains(e.target)) {
			c.classList.remove('telkari-open');
		}
	});
})();
