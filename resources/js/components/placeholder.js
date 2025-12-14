export function initSelectPlaceholders() {
    const selector = 'select.form-select[data-placeholder]';

    function update(el) {
        if (!el) return;
        // Treat empty string or no selection as placeholder
        if (el.value === '' || (el.selectedIndex === 0 && el.value === '')) {
            el.classList.add('placeholder-empty');
        } else {
            el.classList.remove('placeholder-empty');
        }
    }

    function updateAll() {
        document.querySelectorAll(selector).forEach(update);
    }

    // Update on change (covers user interaction)
    document.addEventListener('change', (e) => {
        if (e.target && e.target.matches(selector)) update(e.target);
    });

    // Initial run
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', updateAll);
    } else {
        updateAll();
    }

    // Livewire: update after DOM updates
    try {
        if (window.livewire) {
            document.addEventListener('livewire:load', updateAll);
            document.addEventListener('livewire:update', updateAll);
            document.addEventListener('livewire:message.processed', updateAll);
        }
    } catch (e) {
        // ignore
    }
}
