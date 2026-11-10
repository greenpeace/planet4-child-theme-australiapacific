const themeUrl = document.currentScript.src.replace(/js\/scott-reef\.js.*$/, '');

const styleHubspotForm = () => {
  const frame = document.querySelector('.sr-signup iframe.hs-form-iframe');
  const doc = frame?.contentDocument;

  if (!doc?.querySelector('form') || doc.getElementById('sr-hubspot-style')) {
    return;
  }

  const link = doc.createElement('link');
  link.id = 'sr-hubspot-style';
  link.rel = 'stylesheet';
  link.href = `${themeUrl}scott-reef-hubspot.min.css`;
  doc.head.append(link);
};

const scrollToY = target => {
  const start = window.scrollY;
  const distance = target - start;
  const duration = 600;
  let startTime;

  const step = time => {
    startTime ??= time;
    const progress = Math.min((time - startTime) / duration, 1);
    const eased = 1 - Math.pow(1 - progress, 3);
    window.scrollTo({top: start + distance * eased, behavior: 'instant'});

    if (progress < 1) {
      requestAnimationFrame(step);
    }
  };

  requestAnimationFrame(step);
};

const addEmailLink = () => {
  const menu = document.querySelector('.secondary-navigation-menu');
  const form = document.getElementById('dogooder');

  if (!menu || !form || menu.querySelector('.sr-nav-email')) {
    return;
  }

  const link = document.createElement('a');
  link.className = 'sr-nav-email';
  link.href = '#dogooder';
  link.textContent = 'Send an email ↑';
  link.addEventListener('click', event => {
    event.preventDefault();
    event.stopPropagation();
    scrollToY(form.getBoundingClientRect().top + window.scrollY - 120);
  });
  menu.append(link);
};

const mountCarousel = list => {
  const root = document.createElement('div');
  const track = document.createElement('div');
  root.className = 'splide sr-carousel-wrap';
  track.className = 'splide__track';
  list.before(root);
  root.append(track);
  track.append(list);
  list.classList.add('splide__list');
  [...list.children].forEach(slide => slide.classList.add('splide__slide'));

  new window.Splide(root, {
    arrows: false,
    pagination: false,
    gap: '20px',
    fixedWidth: '500px',
    trimSpace: 'move',
    breakpoints: {
      600: {fixedWidth: '85vw'},
    },
  }).mount();
};

const wrapArrows = link => {
  const walker = document.createTreeWalker(link, NodeFilter.SHOW_TEXT);
  const nodes = [];

  while (walker.nextNode()) {
    if (walker.currentNode.nodeValue.includes('↗')) {
      nodes.push(walker.currentNode);
    }
  }

  nodes.forEach(node => {
    const parts = node.nodeValue.split('↗');
    const fragment = document.createDocumentFragment();

    parts.forEach((part, index) => {
      if (index > 0) {
        const arrow = document.createElement('span');
        arrow.className = 'sr-arrow';
        arrow.textContent = '↗';
        fragment.append(arrow);
      }
      fragment.append(part);
    });

    node.replaceWith(fragment);
  });
};

document.querySelectorAll('.sr-carousel').forEach(mountCarousel);
document.querySelectorAll('.page-content a').forEach(wrapArrows);

new MutationObserver(addEmailLink).observe(document.body, {childList: true, subtree: true});
addEmailLink();

const hubspotTimer = setInterval(() => {
  styleHubspotForm();

  if (document.querySelector('.sr-signup iframe')?.contentDocument?.getElementById('sr-hubspot-style')) {
    clearInterval(hubspotTimer);
  }
}, 250);
setTimeout(() => clearInterval(hubspotTimer), 20000);
