import Chart from 'chart.js/auto';
window.Chart = Chart;

import Alpine from 'alpinejs';

// Only attach Alpine to window if it isn't already present.
if (!window.Alpine) {
    window.Alpine = Alpine;
}

// Start Alpine only if it hasn't been started already by another bundle.
try {
    if (!window.__alpine_started && typeof window.Alpine?.start === 'function') {
        window.Alpine.start();
        window.__alpine_started = true;
    }
} catch (e) {
    console.debug('Alpine start skipped', e);
}

// Register hsForm globally if it exists, but avoid double registration.
try {
    if (typeof hsForm !== 'undefined' && typeof Alpine.data === 'function') {
        window.__registeredAlpineData = window.__registeredAlpineData || {};
        if (!window.__registeredAlpineData['hsForm']) {
            try { Alpine.data('hsForm', hsForm); } catch(e) { console.debug('hsForm init error', e); }
            window.__registeredAlpineData['hsForm'] = true;
        }
    }
} catch (e) { console.debug('hsForm init skipped', e); }

// Your other imports
import { initLogoAnimation } from './components/logo-animation.js';
import { initInfoSlideshow } from './components/info-slideshow.js';
import { initCarousel } from './components/carousel.js';
import { initDropdowns } from './components/dropdown.js';
import { initSelectPlaceholders } from './components/placeholder.js';

const safeInit = (fn, name) => {
    try { if (typeof fn === 'function') fn(); }
    catch (err) { console.error(`Initialization failed for ${name}:`, err); }
};

const runInits = () => {
    safeInit(initLogoAnimation, 'initLogoAnimation');
    safeInit(initInfoSlideshow, 'initInfoSlideshow');
    safeInit(initCarousel, 'initCarousel');
    safeInit(initDropdowns, 'initDropdowns');
    safeInit(initSelectPlaceholders, 'initSelectPlaceholders');
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', runInits);
} else {
    runInits();
}

