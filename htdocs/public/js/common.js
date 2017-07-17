$(document).ready(function () {
	$('#nav-button').click(function () { // shows ~ hides nav
		if ($('#nav').css('display') == 'none') {
			$('#nav').slideDown();
		}
		else {
			$('#nav').slideUp();
		}
	});
});