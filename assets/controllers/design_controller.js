import { Controller } from '@hotwired/stimulus';

/*
 * Sélecteur d'aperçu des propositions de refonte graphique.
 * Le choix est mémorisé dans le navigateur ; ?design=brut dans l'URL permet de partager un design.
 */
export default class extends Controller {
    static targets = ['choix'];

    connect() {
        this.marquer(document.documentElement.dataset.design ?? '');
    }

    choisir({ params: { valeur } }) {
        const racine = document.documentElement;
        if (valeur) {
            racine.dataset.design = valeur;
        } else {
            delete racine.dataset.design;
        }
        try {
            localStorage.setItem('genwod-design', valeur);
        } catch {
            // Stockage indisponible : le choix vaut pour cette page seulement.
        }
        const url = new URL(window.location.href);
        if (url.searchParams.has('design')) {
            url.searchParams.delete('design');
            history.replaceState(history.state, '', url);
        }
        this.marquer(valeur);
    }

    marquer(valeur) {
        this.choixTargets.forEach((bouton) => bouton.setAttribute('aria-pressed', String(bouton.dataset.designValeurParam === valeur)));
    }
}
