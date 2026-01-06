import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['dateDisplay', 'yearInput', 'dayInput', 'help'];
    static values = {
        startingSelector: String,
    };

    connect() {
        this.startingField = this._findStartingField();
        this._toggle();
        if (this.startingField) {
            this.startingField.addEventListener('input', this._toggle);
        }
    }

    disconnect() {
        if (this.startingField) {
            this.startingField.removeEventListener('input', this._toggle);
        }
    }

    _findStartingField() {
        if (!this.hasStartingSelectorValue) {
            return null;
        }
        try {
            return document.querySelector(this.startingSelectorValue) ?? null;
        } catch (e) {
            return null;
        }
    }

    _toggle = () => {
        const enabled = this.startingField && this.startingField.value !== '';

        this.dateDisplayTargets.forEach((el) => {
            el.toggleAttribute('disabled', !enabled);
            if (!enabled) {
                el.classList.add('opacity-60', 'cursor-not-allowed');
            } else {
                el.classList.remove('opacity-60', 'cursor-not-allowed');
            }
        });

        if (!enabled) {
            this.yearInputTargets.forEach((el) => el.value = '');
            this.dayInputTargets.forEach((el) => el.value = '');
        }

        if (this.hasHelpTarget) {
            this.helpTarget.classList.toggle('hidden', enabled);
        }
    };
}
