import { Controller } from '@hotwired/stimulus';

/* Partage natif (mobile) ou copie du lien dans le presse-papiers. */
export default class extends Controller {
    static values = { titre: String };

    async partager() {
        const url = window.location.href;
        if (navigator.share) {
            try {
                await navigator.share({ title: this.titreValue, url });
            } catch {
                // Partage annulé par l'utilisateur.
            }
            return;
        }
        try {
            await navigator.clipboard.writeText(url);
            const libelle = this.element.lastChild;
            const avant = libelle.textContent;
            libelle.textContent = ' Lien copié !';
            setTimeout(() => (libelle.textContent = avant), 2000);
        } catch {
            window.prompt('Copiez ce lien :', url);
        }
    }
}
