import { Controller } from '@hotwired/stimulus';

/* Affiche la valeur d'un curseur en direct. */
export default class extends Controller {
    static targets = ['input', 'sortie'];

    maj() {
        this.sortieTarget.textContent = `${this.inputTarget.value} min`;
    }
}
