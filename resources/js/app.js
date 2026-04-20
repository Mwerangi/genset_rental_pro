import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.coaSelectData = function (selectedId) {
    return {
        open: false,
        search: '',
        selected: { id: selectedId || null, label: '' },
        accounts: window.__coaAccounts || [],
        get filtered() {
            if (!this.search) return this.accounts;
            const q = this.search.toLowerCase();
            return this.accounts.filter(a => a.label.toLowerCase().includes(q));
        },
        select(account) {
            this.selected = account;
            this.search = account.label;
            this.open = false;
        },
        init() {
            if (this.selected.id) {
                const found = this.accounts.find(a => a.id == this.selected.id);
                if (found) { this.selected = found; this.search = found.label; }
            }
        }
    };
};

Alpine.start();
