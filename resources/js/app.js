import './bootstrap';

const lightTheme = 'solohours';
const darkTheme = 'solohours-dark';
const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

const applyTheme = (prefersDark) => {
  const theme = prefersDark ? darkTheme : lightTheme;
  document.documentElement.setAttribute('data-theme', theme);
  if (document.body) {
    document.body.setAttribute('data-theme', theme);
  }
};

const attachGa4FormTracking = () => {
  if (typeof window.gtag !== 'function') {
    return;
  }

  document.querySelectorAll('form[data-ga-event]').forEach((form) => {
    form.addEventListener('submit', () => {
      const eventName = form.dataset.gaEvent;

      if (!eventName) {
        return;
      }

      const eventParams = {};

      if (form.dataset.gaCategory) {
        eventParams.event_category = form.dataset.gaCategory;
      }

      if (form.dataset.gaLabel) {
        eventParams.event_label = form.dataset.gaLabel;
      }

      window.gtag('event', eventName, eventParams);
    });
  });
};

applyTheme(mediaQuery.matches);
attachGa4FormTracking();

if (typeof mediaQuery.addEventListener === 'function') {
  mediaQuery.addEventListener('change', (event) => applyTheme(event.matches));
} else if (typeof mediaQuery.addListener === 'function') {
  mediaQuery.addListener((event) => applyTheme(event.matches));
}
