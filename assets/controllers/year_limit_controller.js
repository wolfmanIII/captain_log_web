import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['yearInput'];
    static values = {
        default: Number,
    };

    connect() {
        this.apply();
    }

    onShipChange() {
        this.apply();
    }

    apply() {
        const defaultMin = Number.isFinite(this.defaultValue) ? this.defaultValue : 0;
        let min = defaultMin;

        const option = this.element instanceof HTMLSelectElement
            ? this.element.options[this.element.selectedIndex]
            : null;

        const startYearAttr = option?.dataset.startYear;
        const startYear = startYearAttr !== undefined && startYearAttr !== ''
            ? parseInt(startYearAttr, 10)
            : NaN;

        if (!Number.isNaN(startYear)) {
            min = Math.max(defaultMin, startYear);
        }

        this.yearInputTargets.forEach((input) => {
            input.min = min;
            if (input.value && Number(input.value) < min) {
                input.value = min;
            }
        });
    }
}
