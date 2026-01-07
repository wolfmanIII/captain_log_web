import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['campaign', 'ship'];

    connect() {
        this.applyFilter();
    }

    onCampaignChange() {
        this.applyFilter();
    }

    applyFilter() {
        const campaignId = this.campaignTarget.value;
        const shipSelect = this.shipTarget;
        const options = Array.from(shipSelect.options);

        const hasCampaign = campaignId !== '';
        shipSelect.disabled = !hasCampaign;

        options.forEach((option) => {
            if (option.value === '') {
                option.hidden = false;
                return;
            }

            const optionCampaign = option.dataset.campaign || '';
            option.hidden = !hasCampaign || optionCampaign !== campaignId;
        });

        if (!hasCampaign) {
            shipSelect.value = '';
            return;
        }

        const selectedOption = shipSelect.options[shipSelect.selectedIndex];
        if (selectedOption && selectedOption.value && selectedOption.dataset.campaign !== campaignId) {
            shipSelect.value = '';
        }
    }
}
