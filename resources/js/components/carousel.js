export function initCarousel() {
  const slides = [
    {
      main: "FCEER 2025 Mock Exam Portal",
      sub: "Manage your exams, schedules, and results easily in one secure platform for students and administrators.",
      subsub: "Ready ka na, future Isko?",
      buttons: `
        <a href="/register" class="btn btn-outline">Wait, Pano Yan?</a>
        <a href="/login" class="btn btn-primary">Oo, Tara!</a>`
    },
    {
      main: "FCEER Attendance Tracker Portal",
      sub: "Track student attendance efficiently and keep your records up to date.",
      subsub: "Access your records anytime, anywhere.",
      buttons: `<a href="/login" class="btn btn-primary">Access Tracker</a>`
    },
    {
      main: "FCEER's History",
      sub: "Two decades of staying true to one calling: to serve and inspire.",
      subsub: "Learn more about our journey and mission.",
      buttons: `<a href="/login" class="btn btn-primary">Our Mission</a>`
    }
  ];

  // Populate slide content
  slides.forEach((s, i) => {
    const mainEl = document.getElementById(`slide-maintext-${i}`);
    const subEl = document.getElementById(`slide-subtext-${i}`);
    const subsubEl = document.getElementById(`slide-subsubtext-${i}`);
    const btnEl = document.getElementById(`slide-buttons-${i}`);

    if (mainEl) mainEl.textContent = s.main;
    if (subEl) subEl.textContent = s.sub;
    if (subsubEl) subsubEl.textContent = s.subsub;
    if (btnEl) btnEl.innerHTML = s.buttons;
  });

  // Animation logic
  const STAGGER_MS = 200;
  const items = document.querySelectorAll('.carousel-item');

  function fadeIn(idx) {
    const caption = items[idx]?.querySelector('.carousel-caption');
    if (!caption) return;
    [...caption.children].forEach((el, i) => {
      el.style.opacity = 0;
      el.style.transform = 'translateY(20px)';
      setTimeout(() => {
        el.style.transition = 'all 0.6s ease-out';
        el.style.opacity = 1;
        el.style.transform = 'translateY(0)';
      }, i * STAGGER_MS);
    });
  }

  function fadeOut(idx) {
    const caption = items[idx]?.querySelector('.carousel-caption');
    if (!caption) return;
    [...caption.children].forEach(el => {
      el.style.transition = '';
      el.style.opacity = 0;
      el.style.transform = 'translateY(20px)';
    });
  }

  items.forEach(item => {
    item.addEventListener('slide.bs.carousel', () => {
      const activeIndex = [...items].findIndex(el => el.classList.contains('active'));
      fadeOut(activeIndex);
    });
    item.addEventListener('slid.bs.carousel', () => {
      const activeIndex = [...items].findIndex(el => el.classList.contains('active'));
      fadeIn(activeIndex);
    });
  });

  const initialIndex = [...items].findIndex(el => el.classList.contains('active'));
  if (initialIndex >= 0) fadeIn(initialIndex);
}
