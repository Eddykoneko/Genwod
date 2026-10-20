import { Controller } from '@hotwired/stimulus';

/* Jauge indicative de robustesse du mot de passe (la vraie vérification est faite côté serveur). */
export default class extends Controller {
    connect() {
        this.jauge = document.createElement('div');
        this.jauge.className = 'strength';
        this.jauge.setAttribute('aria-hidden', 'true');
        this.jauge.innerHTML = '<i></i>';
        this.element.after(this.jauge);
        this.element.addEventListener('input', this.maj);
    }

    disconnect() {
        this.element.removeEventListener('input', this.maj);
        this.jauge.remove();
    }

    maj = () => {
        const v = this.element.value;
        let score = Math.min(4, Math.floor(v.length / 4));
        score += [/[a-z]/, /[A-Z]/, /\d/, /[^\w]/].filter((r) => r.test(v)).length - 1;
        score = Math.max(0, Math.min(5, score)) / 5;
        const barre = this.jauge.firstChild;
        barre.style.width = `${score * 100}%`;
        barre.style.background = score < 0.4 ? 'var(--danger)' : score < 0.8 ? 'var(--gold)' : 'var(--success)';
    };
}
