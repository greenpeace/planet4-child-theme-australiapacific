window.onscroll = function () {
	growShrinkLogo();
};

let size = 0;

function growShrinkLogo() {
  const logo = document.getElementById('logo');
  const header = document.getElementById('header');
  const endOfDocumentTop = 1;
  const scroll =
		window.pageYOffset ||
		document.documentElement.scrollTop ||
		document.body.scrollTop ||
		0;

  if (size == 0 && scroll > endOfDocumentTop) {
		logo.classList.remove( 'largelogo' );
    logo.classList.add('smalllogo');
		header.classList.add( 'white-background' ); // Apply white background and shadow
		size = 1;
	} else if ( size == 1 && scroll <= endOfDocumentTop ) {
		logo.classList.remove( 'smalllogo' );
		logo.classList.add( 'largelogo' );
		header.classList.remove( 'white-background' ); // Remove white background and shadow
		size = 0;
	}
}
