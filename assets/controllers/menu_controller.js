import { Controller } from '@hotwired/stimulus';

/* Menu déroulant basé sur <details> : se ferme au clic extérieur et avec Échap. */
export default class extends Controller {
    fermerSiDehors(event) {
        if (!this.element.contains(event.target)) {
            this.fermer();
        }
    }

    fermer() {
        this.element.open = false;
    }
}
