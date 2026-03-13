const navToggle = document.querySelector('.nav-toggle');
const siteNav = document.querySelector('.site-nav');
const themeToggle = document.querySelector('.theme-toggle');

if (navToggle && siteNav) {
  navToggle.addEventListener('click', () => {
    const expanded = navToggle.getAttribute('aria-expanded') === 'true';
    navToggle.setAttribute('aria-expanded', String(!expanded));
    siteNav.dataset.open = String(!expanded);
  });
}

const savedTheme = localStorage.getItem('theme-preference');
if (savedTheme === 'light' || savedTheme === 'dark') {
  document.documentElement.dataset.theme = savedTheme;
}

if (themeToggle) {
  themeToggle.addEventListener('click', () => {
    const current = document.documentElement.dataset.theme;
    const next = current === 'dark' ? 'light' : 'dark';
    document.documentElement.dataset.theme = next;
    localStorage.setItem('theme-preference', next);
  });
}

const images = document.querySelectorAll('[data-profile-image]');
images.forEach((img) => {
  const wrapper = img.closest('[data-image-wrapper]');
  if (!wrapper) return;

  const applyImageState = () => {
    if (img.complete && img.naturalWidth > 0) {
      wrapper.classList.add('has-image');
    } else {
      wrapper.classList.remove('has-image');
    }
  };

  img.addEventListener('load', applyImageState);
  img.addEventListener('error', applyImageState);
  applyImageState();
});

const year = document.getElementById('year');
if (year) year.textContent = String(new Date().getFullYear());
