export function initDropdowns() {
  const dropdowns = document.querySelectorAll('.dropdown');

  dropdowns.forEach(dropdown => {
    const toggle = dropdown.querySelector('.dropdown-toggle');
    if (toggle) {
      // ensure correct ARIA defaults
      toggle.setAttribute('aria-haspopup', 'true');
      toggle.setAttribute('aria-expanded', 'false');
      const menu = dropdown.querySelector('.dropdown-menu');
      if (menu) menu.setAttribute('aria-hidden', 'true');

      toggle.addEventListener('click', (e) => {
        e.preventDefault();

        // Close other dropdowns
        dropdowns.forEach(other => {
          if (other !== dropdown) {
            other.classList.remove('active');
            const ot = other.querySelector('.dropdown-toggle');
            const om = other.querySelector('.dropdown-menu');
            if (ot) ot.setAttribute('aria-expanded', 'false');
            if (om) om.setAttribute('aria-hidden', 'true');
          }
        });

        // Toggle current dropdown
        const isActive = dropdown.classList.toggle('active');
        toggle.setAttribute('aria-expanded', isActive ? 'true' : 'false');
        if (menu) menu.setAttribute('aria-hidden', isActive ? 'false' : 'true');
        if (isActive) {
          // move focus to first focusable item inside menu for keyboard users
          const first = menu.querySelector('a, button, [tabindex]:not([tabindex="-1"])');
          if (first) first.focus();
        }
      });

      // keyboard: close on Escape, toggle with Enter/Space
      toggle.addEventListener('keydown', (e) => {
        if (e.key === ' ' || e.key === 'Enter') {
          e.preventDefault();
          toggle.click();
        }
      });

      // handle Escape and keyboard navigation inside menu
      dropdown.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          dropdown.classList.remove('active');
          toggle.setAttribute('aria-expanded', 'false');
          if (menu) menu.setAttribute('aria-hidden', 'true');
          toggle.focus();
        }
      });
    }
  });

  // Close dropdown when clicking outside
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.dropdown')) {
      dropdowns.forEach(dropdown => {
        dropdown.classList.remove('active');
        const ot = dropdown.querySelector('.dropdown-toggle');
        const om = dropdown.querySelector('.dropdown-menu');
        if (ot) ot.setAttribute('aria-expanded', 'false');
        if (om) om.setAttribute('aria-hidden', 'true');
      });
    }
  });
}
