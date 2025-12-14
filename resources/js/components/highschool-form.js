// Alpine data for Highschool form: compute abbreviation and title-case locally,
// then sync with Livewire via $wire.set. Kept minimal to avoid Blade parsing issues.
document.addEventListener('alpine:init', () => {
  Alpine.data('hsForm', () => ({
    name: '',
    abbreviationTouched: false,
    stopWords: ['the','and','of','in','a','an','for','to','on','at','by','with','vs','or'],

    computeAbbr(s){
      if(!s || !s.trim()) return '';
      const words = s.split(/[^A-Za-z0-9\u00C0-\u024F\u1E00-\u1EFF]+/).filter(Boolean);
      const tokens = [];
      for(let w of words){
        const lw = w.toLowerCase();
        if(this.stopWords.indexOf(lw) !== -1) continue;
        const cleaned = w.replace(/[^A-Za-z0-9\u00C0-\u024F\u1E00-\u1EFF]/g, '');
        if(!cleaned) continue;
        if(['highschool','high-school','high_school'].indexOf(lw) !== -1){ tokens.push('HS'); continue; }
        tokens.push(cleaned.charAt(0).toUpperCase());
      }
      return tokens.join('');
    },

    titleCase(s){
      if (s === undefined || s === null) return '';
      // preserve leading/trailing whitespace so user can type spaces without them
      // being immediately stripped while typing
      const prefixMatch = s.match(/^\s*/);
      const suffixMatch = s.match(/\s*$/);
      const prefix = prefixMatch ? prefixMatch[0] : '';
      const suffix = suffixMatch ? suffixMatch[0] : '';
      const core = s.trim();
      if (core === '') return prefix + suffix;
      const parts = core.split(/(\s+)/);
      let result = '';
      let wordCount = 0;
      for(let part of parts){
        if(/^\s+$/.test(part)) { result += part; continue; }
        wordCount++;
        const lw = part.toLowerCase();
        if(wordCount === 1){ result += part.charAt(0).toUpperCase() + part.slice(1).toLowerCase(); continue; }
        if(this.stopWords.indexOf(lw) !== -1){ result += lw; continue; }
        result += part.charAt(0).toUpperCase() + part.slice(1).toLowerCase();
      }
      return prefix + result + suffix;
    },

    onNameInput(e){
      const el = e.target;
      if(!el) return;
      const prevPos = (typeof el.selectionStart === 'number') ? el.selectionStart : null;
      const prevLen = el.value.length;
      let s = el.value;
      // previous auto-computed abbreviation based on the current known name
      const oldAuto = this.computeAbbr(this.name || '');
      const newName = this.titleCase(s);
      if(newName !== s){
        try{ el.value = newName; }catch(_){ }
        // restore caret position relative to end change
        try{
          if (prevPos !== null) {
            const delta = newName.length - prevLen;
            const newPos = Math.max(0, prevPos + delta);
            el.setSelectionRange(newPos, newPos);
          }
        } catch(e){}
      }
      this.name = newName;
      const abbr = this.computeAbbr(newName);
      const abEl = this.$root ? (this.$root.querySelector ? this.$root.querySelector('#abbreviation') : null) : document.getElementById('abbreviation');
      const current = abEl ? abEl.value : '';
      // Overwrite abbreviation when:
      // - the user hasn't touched it, AND
      //   - it's empty, OR
      //   - it equals the previous auto-computed abbreviation (we can safely update), OR
      //   - it's a short placeholder (single char) and not touched (common when user just started typing)
      const shouldAutoOverwrite = !this.abbreviationTouched && (
        !current ||
        current.length === 0 ||
        current === oldAuto ||
        (current.length === 1 && oldAuto && oldAuto.startsWith(current))
      );
      if (shouldAutoOverwrite) {
        try{
            // Update Alpine model instead of touching DOM directly so x-model stays consistent.
            this.abbreviation = abbr;
            // Update Livewire properties, but debounce to avoid excessively frequent XHRs while typing.
            this._debounceSync = this._debounceSync || null;
            if (this._debounceSync) clearTimeout(this._debounceSync);
            this._debounceSync = setTimeout(() => {
              try { if (this.$wire && typeof this.$wire.set === 'function') { this.$wire.set('name', this.name); this.$wire.set('abbreviation', this.abbreviation); this.$wire.set('abbreviationTouched', this.abbreviationTouched); } } catch(e) {}
            }, 250);
        } catch(e){}
      }
      // Also schedule a sync if we didn't overwrite abbreviation (to keep server name up-to-date)
      if (!shouldAutoOverwrite) {
        this._debounceSync = this._debounceSync || null;
        if (this._debounceSync) clearTimeout(this._debounceSync);
        this._debounceSync = setTimeout(() => {
          try { if (this.$wire && typeof this.$wire.set === 'function') { this.$wire.set('name', this.name); this.$wire.set('abbreviationTouched', this.abbreviationTouched); } } catch(e) {}
        }, 250);
      }
    },

    onAbbreviationInput(e){
      this.abbreviationTouched = true;
      // Update Alpine model (user typing)
      try{
        this.abbreviation = e.target.value;
      } catch(e){}
      // Debounced server sync for abbreviation to avoid frequent XHRs
      this._debounceAbbr = this._debounceAbbr || null;
      if (this._debounceAbbr) clearTimeout(this._debounceAbbr);
      const val = e.target.value;
      this._debounceAbbr = setTimeout(() => {
        try { if (this.$wire && typeof this.$wire.set === 'function') { this.$wire.set('abbreviation', val); this.$wire.set('abbreviationTouched', true); } } catch(e) {}
      }, 200);
    },

    initFromDom(){
      try{
        const nameEl = this.$root ? this.$root.querySelector('#name') : document.getElementById('name');
        const abEl = this.$root ? this.$root.querySelector('#abbreviation') : document.getElementById('abbreviation');
        if(nameEl) this.name = nameEl.value || '';
        if(abEl) {
          this.abbreviation = abEl.value || '';
          this.abbreviationTouched = !!(abEl.value && abEl.value.length > 0);
        }
      }catch(e){}
    }
  }));
});
