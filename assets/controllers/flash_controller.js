import { Controller } from '@hotwired/stimulus';

/* Message flash qui disparaît tout seul. */
export default class extends Controller {
    connect() {
        this.minuteur = setTimeout(() => this.fermer(), 6000);
    }

    disconnect() {
        clearTimeout(this.minuteur);
    }

    fermer() {
        this.element.classList.add('leaving');
        setTimeout(() => this.element.remove(), 250);
    }
}
