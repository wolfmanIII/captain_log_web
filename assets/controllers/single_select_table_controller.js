import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["radio", "hidden", "row"];

    connect() {
        this.highlightSelectedRow();
    }

    select(event) {
        const value = event.target.dataset.value;

        // aggiorna hidden input Symfony
        this.hiddenTarget.value = value;

        this.highlightSelectedRow();
    }

    highlightSelectedRow() {
        const selectedValue = this.hiddenTarget.value;

        // rimuovi evidenziazione da tutte le righe
        this.rowTargets.forEach(row => {
            row.classList.remove("bg-primary/10");
        });

        // evidenzia la riga del radio selezionato
        this.radioTargets.forEach((radio, index) => {
            if (radio.dataset.value === selectedValue) {
                this.rowTargets[index].classList.add("bg-primary/10");
            }
        });
    }
}
