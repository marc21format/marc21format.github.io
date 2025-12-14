export function initLogoAnimation() {
  const LOGO_DELAY = 3000;
  const logos = document.querySelectorAll('.logo-slide');
  let logoIndex = 0;

  // Respect reduced motion
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (logos.length > 0 && !prefersReduced) {
    let intervalId = setInterval(() => {
      // Fade out current
      logos[logoIndex].classList.add('fade-out');
      logos[logoIndex].classList.remove('active');

      logoIndex = (logoIndex + 1) % logos.length;

      // Fade in next after delay
      setTimeout(() => {
        logos.forEach(l => l.classList.remove('fade-out'));
        logos[logoIndex].classList.add('active');
      }, 400);
    }, LOGO_DELAY);

    // Pause on hover / focus
    logos.forEach((el) => {
      el.addEventListener('mouseenter', () => clearInterval(intervalId));
      el.addEventListener('focusin', () => clearInterval(intervalId));
      el.addEventListener('mouseleave', () => {
        intervalId = setInterval(() => {
          logos[logoIndex].classList.add('fade-out');
          logos[logoIndex].classList.remove('active');

          logoIndex = (logoIndex + 1) % logos.length;

          setTimeout(() => {
            logos.forEach(l => l.classList.remove('fade-out'));
            logos[logoIndex].classList.add('active');
          }, 400);
        }, LOGO_DELAY);
      });
      el.addEventListener('focusout', () => {
        intervalId = setInterval(() => {
          logos[logoIndex].classList.add('fade-out');
          logos[logoIndex].classList.remove('active');

          logoIndex = (logoIndex + 1) % logos.length;

          setTimeout(() => {
            logos.forEach(l => l.classList.remove('fade-out'));
            logos[logoIndex].classList.add('active');
          }, 400);
        }, LOGO_DELAY);
      });
    });
  }
}
