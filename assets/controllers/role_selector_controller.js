import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['select', 'chips'];

    connect() {
        if (!this.selectTarget || !this.chipsTarget) {
            return;
        }

        this.renderChips();
    }

    renderChips() {
        this.chipsTarget.innerHTML = '';
        Array.from(this.selectTarget.options).forEach(option => {
            if (!option.value) {
                return;
            }

            const chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'btn btn-xs btn-outline flex items-center gap-1';
            if (option.selected) {
                chip.classList.add('btn-primary');
                chip.classList.remove('btn-outline');
            }
            chip.textContent = option.textContent.trim();
            chip.dataset.value = option.value;
            chip.addEventListener('click', () => this.toggleRole(option.value));
            this.chipsTarget.appendChild(chip);
        });
    }

    toggleRole(value) {
        const option = Array.from(this.selectTarget.options).find(opt => opt.value === value);
        if (!option) {
            return;
        }

        option.selected = !option.selected;
        this.renderChips();
    }
}
