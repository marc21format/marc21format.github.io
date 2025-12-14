@extends('layouts.app')

@section('content')
    <div class="container">
        @livewire('degree-programs.edit', ['degreeprogram_id' => $id])
    </div>


    <script>
    document.addEventListener('DOMContentLoaded', function(){
        const levelSel = document.getElementById('dp-level-select');
        const typeSel = document.getElementById('dp-type-select');
        const fieldSel = document.getElementById('dp-field-select');
    const nameInput = document.getElementById('dp-name-input');
    const abbrInput = document.getElementById('dp-abbr-input');

        function getSelectedText(sel){
            if(!sel) return '';
            const opt = sel.options[sel.selectedIndex];
            if(!opt) return '';
            const val = (opt.value || '').toString().trim();
            const txt = (opt.text || '').toString().trim();
            if(val === '' || txt === '--' || txt === '—' || txt === '–') return '';
            return txt;
        }
        function getSelectedAbbr(sel){
            if(!sel) return '';
            const opt = sel.options[sel.selectedIndex];
            return opt ? (opt.dataset.abbr || '').trim() : '';
        }

        function buildProgram(){
            const levelText = getSelectedText(levelSel);
            const typeText = getSelectedText(typeSel);
            const fieldText = getSelectedText(fieldSel);

            let full = '';
            if(levelText && typeText){
                full = `${levelText} of ${typeText}` + (fieldText ? ` in ${fieldText}` : '');
            } else if(levelText && fieldText){
                // no type selected -> skip the 'in' per rule: produce "Level of Field"
                full = `${levelText} of ${fieldText}`;
            } else if(levelText){
                full = levelText;
            }

            // abbreviations concatenation (join non-empty without separator)
            const parts = [];
            const lAbbr = getSelectedAbbr(levelSel);
            const tAbbr = getSelectedAbbr(typeSel);
            const fAbbr = getSelectedAbbr(fieldSel);
            if(lAbbr) parts.push(lAbbr);
            if(tAbbr) parts.push(tAbbr);
            if(fAbbr) parts.push(fAbbr);
            const abbr = parts.join('');

            if(nameInput) nameInput.value = full;
            if(abbrInput) abbrInput.value = abbr;

            // trigger input events so Livewire sees updated values on next request
            if(nameInput){
                nameInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
            if(abbrInput){
                abbrInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }

        // use event delegation so handlers survive Livewire DOM patches
        document.addEventListener('change', function(e){
            if(e.target && (e.target.id === 'dp-level-select' || e.target.id === 'dp-type-select' || e.target.id === 'dp-field-select')){
                buildProgram();
            }
        });

        // also run when Livewire updates the DOM (useful if server-side sets selected values)
        window.addEventListener('livewire:update', function(){
            buildProgram();
        });

        // run once on load to prefill from any server-side selected values
        buildProgram();
    });
    </script>

@endsection
