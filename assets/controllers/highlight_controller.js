import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        const hljs = window.hljs;
        if (!hljs) {
            return;
        }

        this.element.querySelectorAll('code').forEach((block) => {
            hljs.highlightElement(block);
        });
    }
}
