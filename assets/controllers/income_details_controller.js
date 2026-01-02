import { Controller } from "@hotwired/stimulus";
import { Turbo } from "@hotwired/turbo";

export default class extends Controller {
    change(event) {
        const value = event.target.value;
        const url = new URL(window.location.href);

        if (value) {
            url.searchParams.set("category", value);
        } else {
            url.searchParams.delete("category");
        }

        Turbo.visit(url.toString(), { action: "replace" });
    }
}
