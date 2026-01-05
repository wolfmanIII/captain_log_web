import { Controller } from '@hotwired/stimulus';

const MONTHS = [
    { label: 'Holiday (001)', value: 'holiday', start: 1, end: 1 },
    { label: 'Month 1', value: '1', start: 2, end: 29 },
    { label: 'Month 2', value: '2', start: 30, end: 57 },
    { label: 'Month 3', value: '3', start: 58, end: 85 },
    { label: 'Month 4', value: '4', start: 86, end: 113 },
    { label: 'Month 5', value: '5', start: 114, end: 141 },
    { label: 'Month 6', value: '6', start: 142, end: 169 },
    { label: 'Month 7', value: '7', start: 170, end: 197 },
    { label: 'Month 8', value: '8', start: 198, end: 225 },
    { label: 'Month 9', value: '9', start: 226, end: 253 },
    { label: 'Month 10', value: '10', start: 254, end: 281 },
    { label: 'Month 11', value: '11', start: 282, end: 309 },
    { label: 'Month 12', value: '12', start: 310, end: 337 },
    { label: 'Month 13', value: '13', start: 338, end: 365 },
];

export default class extends Controller {
    static targets = ['month', 'dayInMonth', 'day'];

    connect() {
        const initialDay = this.dayTarget.dataset.initialDay;
        if (initialDay) {
            this.setFromAbsoluteDay(parseInt(initialDay, 10));
        }
        this.ensureDayChoices();
        this.updateDayValue();
    }

    onMonthChange() {
        this.ensureDayChoices();
        this.updateDayValue();
    }

    onDayChange() {
        this.updateDayValue();
    }

    ensureDayChoices() {
        const month = this.findMonth(this.monthTarget.value);
        if (!month) {
            this.dayInMonthTarget.innerHTML = '';
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = '-- Day --';
            this.dayInMonthTarget.appendChild(placeholder);
            return;
        }

        const currentValue = this.dayInMonthTarget.value;
        this.dayInMonthTarget.innerHTML = '';

        for (let day = month.start; day <= month.end; day += 1) {
            const option = document.createElement('option');
            const dayInMonth = day - month.start + 1;
            option.value = String(dayInMonth);
            option.textContent = `${dayInMonth} (${String(day).padStart(3, '0')})`;
            if (option.value === currentValue) {
                option.selected = true;
            }
            this.dayInMonthTarget.appendChild(option);
        }
    }

    updateDayValue() {
        const month = this.findMonth(this.monthTarget.value);
        const dayInMonth = parseInt(this.dayInMonthTarget.value, 10);

        if (!month || Number.isNaN(dayInMonth)) {
            this.dayTarget.value = '';
            return;
        }

        const absoluteDay = month.start + (dayInMonth - 1);
        this.dayTarget.value = absoluteDay;
    }

    setFromAbsoluteDay(day) {
        const month = MONTHS.find((m) => day >= m.start && day <= m.end);
        if (!month) {
            return;
        }

        this.monthTarget.value = month.value;
        const dayInMonth = day - month.start + 1;
        this.ensureDayChoices();
        this.dayInMonthTarget.value = String(dayInMonth);
    }

    findMonth(value) {
        return MONTHS.find((m) => String(m.value) === String(value)) || null;
    }
}
