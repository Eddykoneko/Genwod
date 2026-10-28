import { Controller } from '@hotwired/stimulus';

/* Bascule thème clair / sombre, mémorisé dans le navigateur. */
export default class extends Controller {
    basculer() {
        const racine = document.documentElement;
        const actuel = racine.dataset.theme ?? (matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark');
        const suivant = actuel === 'light' ? 'dark' : 'light';
        racine.dataset.theme = suivant;
        const meta = document.querySelector('meta[name="theme-color"]');
        meta?.setAttribute('content', (suivant === 'light' ? meta.dataset.clair : meta.dataset.sombre) ?? (suivant === 'light' ? '#f6f5f1' : '#0d0e10'));
        try {
            localStorage.setItem('genwod-theme', suivant);
        } catch {
            // Stockage indisponible (navigation privée) : le choix ne sera pas mémorisé.
        }
    }
}
