import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    change(event) {
        const value = event.target.value;
        const url = new URL(window.location.href);

        if (value) {
            url.searchParams.set("category", value);
        } else {
            url.searchParams.delete("category");
        }

        if (window.Turbo && typeof window.Turbo.visit === "function") {
            window.Turbo.visit(url.toString(), { action: "replace" });
        } else {
            window.location.href = url.toString();
        }
    }
}
