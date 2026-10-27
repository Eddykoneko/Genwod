import { Controller } from '@hotwired/stimulus';

/*
 * Chrono plein écran adapté au format du WOD :
 *  - For Time : chrono qui monte jusqu'au time cap, bouton « Terminé » qui reporte le temps dans le formulaire ;
 *  - AMRAP    : compte à rebours + compteur de tours reporté dans le formulaire ;
 *  - EMOM     : une minute par mouvement, en boucle ;
 *  - Death By : chaque minute, un pas de plus (1, 2, 3… répétitions) ; bouton « Minute ratée » qui reporte les minutes complétées ;
 *  - For Load / Max reps : chrono libre (repos à volonté entre les séries) ;
 *  - Intervalles (Fight Gone Bad, The Chief) : blocs de travail, une station par mouvement ou non, puis repos.
 * Décompte de 10 s, bips (Web Audio) et écran maintenu allumé (Wake Lock API).
 */
const DECOMPTE = 10;

export default class extends Controller {
    static values = { type: String, duree: Number, lignes: Array, pas: Array, blocs: Number, travail: Number, repos: Number, parStation: Boolean };
    static targets = ['dialog', 'ecran', 'phase', 'temps', 'sous', 'barre', 'tours', 'boutonDemarrer', 'boutonPause', 'boutonFini'];

    connect() {
        this.reinitialiser();
    }

    disconnect() {
        this.arreterBoucle();
        this.libererEcran();
    }

    ouvrir() {
        this.dialogTarget.showModal();
    }

    fermer(event) {
        event?.preventDefault();
        this.pause();
        this.dialogTarget.close();
    }

    demarrer() {
        this.audio ??= new (window.AudioContext || window.webkitAudioContext)();
        if (this.etat === 'fini') {
            this.reinitialiser();
        }
        this.debut = performance.now() - this.ecoule;
        this.enCours = true;
        this.boutonDemarrerTarget.hidden = true;
        this.boutonPauseTarget.hidden = false;
        this.garderEcranAllume();
        this.boucle = setInterval(() => this.tic(), 100);
        this.tic();
    }

    pause() {
        if (!this.enCours) {
            return;
        }
        this.enCours = false;
        this.arreterBoucle();
        this.libererEcran();
        this.boutonPauseTarget.hidden = true;
        this.boutonDemarrerTarget.hidden = false;
        this.boutonDemarrerTarget.lastChild.textContent = ' Reprendre';
        this.phaseTarget.textContent = 'En pause';
    }

    reinitialiser() {
        this.arreterBoucle();
        this.libererEcran();
        this.ecoule = 0;
        this.enCours = false;
        this.tours = 0;
        this.bipsJoues = new Set();
        this.etat = 'pret';
        if (this.hasToursTarget) this.toursTarget.textContent = '0';
        if (this.hasBoutonFiniTarget) this.boutonFiniTarget.hidden = true;
        this.boutonPauseTarget.hidden = true;
        this.boutonDemarrerTarget.hidden = false;
        this.boutonDemarrerTarget.lastChild.textContent = ' Démarrer';
        const depart = this.blocsValue ? this.travailValue : this.typeValue === 'amrap' ? this.total() : 0;
        this.afficher('pret', 'Prêt ?', this.format(depart), this.premiereLigne(), 0);
    }

    tourPlus() {
        this.tours++;
        this.toursTarget.textContent = String(this.tours);
        this.bip(660, 0.08);
    }

    /**
     * For Time : l'athlète a fini, on fige le temps et on le reporte dans le formulaire.
     * Death By : la minute en cours est ratée, on reporte les minutes complétées.
     */
    terminer() {
        const secondes = Math.round(Math.max(0, this.ecoule / 1000 - DECOMPTE));
        if (this.typeValue === 'death_by') {
            const minutes = Math.floor(secondes / 60);
            this.finir('Minute ratée', `${minutes} min`, 'Ajoutez les répétitions de la minute ratée dans « Noter mon score »');
            this.reporter('tours', String(minutes));
            return;
        }
        this.finir('Bravo !', this.format(secondes));
        this.reporter('temps', this.format(secondes));
    }

    // ---------------------------------------------------------------

    tic() {
        this.ecoule = performance.now() - this.debut;
        const t = this.ecoule / 1000;

        if (t < DECOMPTE) {
            const reste = Math.ceil(DECOMPTE - t);
            this.bipUneFois(`d${reste}`, reste <= 3 ? 880 : null);
            this.afficher('decompte', 'Départ dans', String(reste), this.premiereLigne(), 0);
            return;
        }
        this.bipUneFois('go', 1320, 0.5);

        const e = t - DECOMPTE;
        const total = this.total();
        if (e >= total) {
            this.bip(1320, 0.8);
            const sous = ['max_reps', 'for_load'].includes(this.typeValue) ? 'Notez votre score dans « Noter mon score »' : undefined;
            this.finir(this.typeValue === 'for_time' ? 'Time cap !' : 'Terminé !', this.typeValue === 'amrap' ? '0:00' : this.format(total), sous);
            if (this.typeValue === 'amrap') this.reporter('tours', String(this.tours));
            if (this.typeValue === 'death_by') this.reporter('tours', String(this.dureeValue));
            if (this.typeValue === 'max_reps') this.reporter('reps', '');
            if (this.typeValue === 'for_load') this.reporter('charge', '');
            return;
        }

        if (this.blocsValue) {
            this.ticIntervalles(e, total);
            return;
        }

        switch (this.typeValue) {
            case 'for_time':
                if (this.hasBoutonFiniTarget) this.boutonFiniTarget.hidden = false;
                this.afficher('effort', 'For Time', this.format(e), `Time cap ${this.format(total)}`, e / total);
                break;
            case 'amrap':
                this.bipFinDePeriode(total - e, 'fin');
                this.afficher('effort', 'AMRAP', this.format(total - e), 'Max de tours !', e / total);
                break;
            case 'emom': {
                const minute = Math.floor(e / 60);
                const reste = 60 - (e % 60);
                this.bipUneFois(`m${minute}`, minute > 0 ? 1320 : null, 0.4);
                this.bipFinDePeriode(reste, `m${minute}`);
                this.afficher('effort', `Minute ${minute + 1} / ${this.dureeValue}`, this.format(reste), this.ligne(minute), e / total);
                break;
            }
            case 'for_load':
            case 'max_reps':
                this.afficher('effort', 'Chrono libre', this.format(e), 'Repos à volonté entre les séries', e / total);
                break;
            case 'death_by': {
                const minute = Math.floor(e / 60);
                const reste = 60 - (e % 60);
                if (this.hasBoutonFiniTarget) this.boutonFiniTarget.hidden = false;
                this.bipUneFois(`m${minute}`, minute > 0 ? 1320 : null, 0.4);
                this.bipFinDePeriode(reste, `m${minute}`);
                this.afficher('effort', `Minute ${minute + 1}`, this.format(reste), this.travailDeathBy(minute), e / total);
                break;
            }
        }
    }

    /** Blocs de travail (une période par station, ou une seule pour tous les mouvements) séparés par un repos. */
    ticIntervalles(e, total) {
        const periodes = this.periodes();
        const travailBloc = periodes * this.travailValue;
        const cycle = travailBloc + this.reposValue;
        const bloc = Math.floor(e / cycle);
        const dansCycle = e - bloc * cycle;
        const titreBloc = `Tour ${bloc + 1} / ${this.blocsValue}`;

        if (dansCycle >= travailBloc) {
            const cle = `r${bloc}`;
            this.bipUneFois(cle, 520, 0.35);
            this.bipFinDePeriode(cycle - dansCycle, cle);
            this.afficher('repos', `Repos · ${titreBloc}`, this.format(cycle - dansCycle), `Ensuite : ${this.parStationValue ? this.ligne(0) : `tour ${bloc + 2}`}`, e / total);
            return;
        }

        const periode = Math.floor(dansCycle / this.travailValue);
        const cle = `i${bloc}-${periode}`;
        this.bipUneFois(cle, e > 0 ? 1320 : null, 0.4);
        this.bipFinDePeriode(this.travailValue - (dansCycle % this.travailValue), cle);
        this.afficher(
            'effort',
            this.parStationValue ? `${titreBloc} · station ${periode + 1} / ${periodes}` : titreBloc,
            this.format(this.travailValue - (dansCycle % this.travailValue)),
            this.parStationValue ? this.ligne(periode) : this.typeValue === 'amrap' ? 'Max de tours !' : 'Max de répétitions !',
            e / total,
        );
    }

    periodes() {
        return this.parStationValue ? Math.max(1, this.lignesValue.length) : 1;
    }

    finir(phase, temps, sous = 'Score reporté dans « Noter mon score »') {
        this.arreterBoucle();
        this.libererEcran();
        this.enCours = false;
        this.etat = 'fini';
        this.boutonPauseTarget.hidden = true;
        if (this.hasBoutonFiniTarget) this.boutonFiniTarget.hidden = true;
        this.boutonDemarrerTarget.hidden = false;
        this.boutonDemarrerTarget.lastChild.textContent = ' Recommencer';
        this.afficher('fini', phase, temps, sous, 1);
    }

    /** Reporte le résultat dans le champ du formulaire de séance et y amène l'utilisateur. */
    reporter(cible, valeur) {
        const champ = this.element.querySelector(`[data-chrono-cible="${cible}"]`);
        if (!champ) {
            return;
        }
        if (valeur !== '') champ.value = valeur;
        const noter = this.element.querySelector('#noter');
        if (noter) noter.open = true;
        setTimeout(() => {
            this.dialogTarget.close();
            champ.scrollIntoView({ behavior: 'smooth', block: 'center' });
            champ.focus({ preventScroll: true });
        }, 1600);
    }

    afficher(etat, phase, temps, sous, progression) {
        this.etat = etat;
        this.ecranTarget.dataset.etat = etat;
        this.phaseTarget.textContent = phase;
        this.tempsTarget.textContent = temps;
        this.sousTarget.textContent = sous ?? '';
        this.barreTarget.style.width = `${Math.min(100, Math.max(0, progression * 100))}%`;
    }

    total() {
        if (this.blocsValue) {
            return this.blocsValue * this.periodes() * this.travailValue + (this.blocsValue - 1) * this.reposValue;
        }
        return this.dureeValue * 60;
    }

    /** Death By : le travail de la minute (0 = première minute), ex. « 6 Burpees · 6 Tractions ». */
    travailDeathBy(minute) {
        return this.lignesValue.map((ligne, i) => `${(minute + 1) * (this.pasValue[i] ?? 1)} ${ligne}`).join(' · ');
    }

    ligne(index) {
        return this.lignesValue.length ? this.lignesValue[index % this.lignesValue.length] : '';
    }

    premiereLigne() {
        if (this.typeValue === 'death_by') return `D'abord : ${this.travailDeathBy(0)}`;
        if (this.blocsValue && this.parStationValue) return `D'abord : ${this.ligne(0)}`;
        return this.typeValue === 'emom' ? `D'abord : ${this.ligne(0)}` : '';
    }

    format(secondes) {
        const s = Math.max(0, Math.floor(secondes));
        return `${Math.floor(s / 60)}:${String(s % 60).padStart(2, '0')}`;
    }

    // --- Son -------------------------------------------------------

    bip(frequence = 880, duree = 0.15) {
        if (!this.audio) return;
        const osc = this.audio.createOscillator();
        const gain = this.audio.createGain();
        osc.type = 'square';
        osc.frequency.value = frequence;
        gain.gain.setValueAtTime(0.12, this.audio.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.0001, this.audio.currentTime + duree);
        osc.connect(gain).connect(this.audio.destination);
        osc.start();
        osc.stop(this.audio.currentTime + duree);
    }

    /** Joue un bip une seule fois par « clé » (évite de rejouer à chaque tic de 100 ms). */
    bipUneFois(cle, frequence, duree = 0.15) {
        if (this.bipsJoues.has(cle)) return;
        this.bipsJoues.add(cle);
        if (frequence) this.bip(frequence, duree);
    }

    /** 3 bips courts avant la fin d'une période. */
    bipFinDePeriode(reste, periode) {
        const s = Math.ceil(reste);
        if (s <= 3 && s >= 1) this.bipUneFois(`${periode}-${s}`, 880);
    }

    // --- Écran -----------------------------------------------------

    async garderEcranAllume() {
        try {
            this.verrou = await navigator.wakeLock?.request('screen');
        } catch {
            // Non supporté ou refusé : sans gravité.
        }
    }

    libererEcran() {
        this.verrou?.release().catch(() => {});
        this.verrou = null;
    }

    arreterBoucle() {
        clearInterval(this.boucle);
        this.boucle = null;
    }
}
