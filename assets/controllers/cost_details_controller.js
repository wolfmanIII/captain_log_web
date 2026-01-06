import { Controller } from '@hotwired/stimulus';

// Gestione collection detailItems per i costi (add/remove).
export default class extends Controller {
    static targets = ['collection', 'list', 'prototype'];

    connect() {
        // Se non ci sono item, aggiungiamo un prototipo iniziale.
        if (this.hasListTarget && this.listTarget.children.length === 0) {
            this.addFromPrototype(this.collectionTarget);
        }
    }

    addItem(event) {
        const collection = event.currentTarget.closest('[data-cost-details-target="collection"]');
        this.addFromPrototype(collection);
    }

    addFromPrototype(collection) {
        if (!collection) return;
        const list = collection.querySelector('[data-cost-details-target="list"]');
        const templateEl = collection.querySelector('template[data-cost-details-target="prototype"]');
        if (!list || !templateEl) return;

        const index = list.children.length;
        const html = templateEl.innerHTML.replace(/__name__/g, index);
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();
        const newItem = wrapper.firstElementChild;
        list.appendChild(newItem);
    }

    removeItem(event) {
        const item = event.currentTarget.closest('.collection-item');
        if (item) {
            item.remove();
        }
    }
}
