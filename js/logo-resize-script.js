window.onscroll = function () {
	growShrinkLogo();
};

let initiateTransition = 0;

function growShrinkLogo() {
  const logo = document.getElementById('logo');
  const header = document.getElementById('header');
  const initiateTransitionLocation = 1;
  const scroll =
		window.pageYOffset ||
		document.documentElement.scrollTop ||
		document.body.scrollTop ||
		0;

	if (document.body.classList.contains('home')) {
		if (initiateTransition == 0 && scroll > initiateTransitionLocation) {
			logo.classList.remove('largelogo');
			logo.classList.add('smalllogo');
			header.classList.add('white-background'); // Apply white background and shadow
			initiateTransition = 1;
		} else if ( initiateTransition == 1 && scroll <= initiateTransitionLocation ) {
			logo.classList.remove('smalllogo');
			logo.classList.add('largelogo');
			header.classList.remove('white-background'); // Remove white background and shadow
			initiateTransition = 0;
		}
	}
}