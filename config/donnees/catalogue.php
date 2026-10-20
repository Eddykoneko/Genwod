<?php

/*
 * Données de référence de Genwod : catalogue de mouvements et WODs éditoriaux.
 * Importées (sans doublon) par « php bin/console app:catalogue:importer ».
 *
 * Mouvement : [slug, nom, catégorie, équipement, unité, volume de base, charge RX, technique, description]
 * WOD       : [titre, type, difficulté, durée, tours, consignes, [[slug, quantité], …]]
 */

return [
    'mouvements' => [
        // Cardio
        ['course', 'Course', 'cardio', 'aucun', 'm', 400, null, false, 'Course à pied à allure soutenue.'],
        ['rameur', 'Rameur', 'cardio', 'rameur', 'cal', 15, null, false, 'Tirage jambes → buste → bras, retour dans l\'ordre inverse.'],
        ['assault-bike', 'Assault bike', 'cardio', 'velo', 'cal', 15, null, false, 'Vélo à air : poussez et tirez avec les bras.'],
        ['double-unders', 'Double unders', 'cardio', 'corde', 'reps', 40, null, true, 'La corde passe deux fois sous les pieds à chaque saut.'],
        ['single-unders', 'Sauts à la corde', 'cardio', 'corde', 'reps', 80, null, false, 'Sauts simples, sur l\'avant du pied.'],
        ['jumping-jacks', 'Jumping jacks', 'cardio', 'aucun', 'reps', 40, null, false, 'Écart bras-jambes en rythme.'],
        ['shuttle-run', 'Navettes', 'cardio', 'aucun', 'm', 200, null, false, 'Allers-retours de 10 m en touchant le sol.'],

        // Gymnastique
        ['air-squat', 'Air squats', 'gymnastique', 'aucun', 'reps', 20, null, false, 'Hanches sous les genoux, talons au sol.'],
        ['push-up', 'Pompes', 'gymnastique', 'aucun', 'reps', 15, null, false, 'Poitrine au sol, corps gainé.'],
        ['sit-up', 'Sit-ups', 'gymnastique', 'aucun', 'reps', 20, null, false, 'Abdos complets, épaules qui passent les hanches.'],
        ['burpee', 'Burpees', 'gymnastique', 'aucun', 'reps', 12, null, false, 'Poitrine au sol, saut et clap au-dessus de la tête.'],
        ['fentes', 'Fentes alternées', 'gymnastique', 'aucun', 'reps', 20, null, false, 'Genou arrière qui effleure le sol.'],
        ['mountain-climbers', 'Mountain climbers', 'gymnastique', 'aucun', 'reps', 30, null, false, 'En planche, genoux à la poitrine en alternance.'],
        ['v-ups', 'V-ups', 'gymnastique', 'aucun', 'reps', 15, null, false, 'Mains et pieds se rejoignent au-dessus des hanches.'],
        ['hspu', 'Handstand push-ups', 'gymnastique', 'aucun', 'reps', 6, null, true, 'Pompes en équilibre, contre un mur.'],
        ['pull-up', 'Tractions', 'gymnastique', 'barre_traction', 'reps', 10, null, false, 'Menton au-dessus de la barre.'],
        ['toes-to-bar', 'Toes-to-bar', 'gymnastique', 'barre_traction', 'reps', 10, null, true, 'Suspendu, les pieds touchent la barre.'],
        ['chest-to-bar', 'Chest-to-bar', 'gymnastique', 'barre_traction', 'reps', 8, null, true, 'Traction jusqu\'à toucher la barre avec la poitrine.'],
        ['box-jump', 'Box jumps', 'gymnastique', 'box', 'reps', 12, '60/50 cm', false, 'Saut sur la box, extension complète en haut.'],
        ['step-up', 'Step-ups', 'gymnastique', 'box', 'reps', 16, '50/40 cm', false, 'Montée alternée sur la box.'],
        ['burpee-box-jump-over', 'Burpees box jump over', 'gymnastique', 'box', 'reps', 8, '60/50 cm', false, 'Un burpee puis un saut par-dessus la box.'],

        // Haltérophilie
        ['thruster', 'Thrusters', 'halterophilie', 'barre', 'reps', 12, '43/30 kg', false, 'Front squat enchaîné avec un développé au-dessus de la tête.'],
        ['deadlift', 'Soulevés de terre', 'halterophilie', 'barre', 'reps', 10, '100/70 kg', false, 'Dos plat, la barre frôle les jambes.'],
        ['front-squat', 'Front squats', 'halterophilie', 'barre', 'reps', 10, '60/40 kg', false, 'Barre en appui sur les épaules, coudes hauts.'],
        ['push-press', 'Push press', 'halterophilie', 'barre', 'reps', 10, '50/35 kg', false, 'Impulsion des jambes puis développé.'],
        ['power-clean', 'Power cleans', 'halterophilie', 'barre', 'reps', 8, '60/40 kg', true, 'Épaulé réceptionné au-dessus de la parallèle.'],
        ['power-snatch', 'Power snatches', 'halterophilie', 'barre', 'reps', 6, '43/30 kg', true, 'Arraché réceptionné au-dessus de la parallèle.'],
        ['clean-and-jerk', 'Épaulés-jetés', 'halterophilie', 'barre', 'reps', 6, '60/40 kg', true, 'Épaulé puis jeté, bras verrouillés.'],
        ['overhead-squat', 'Overhead squats', 'halterophilie', 'barre', 'reps', 8, '43/30 kg', true, 'Squat complet, barre verrouillée au-dessus de la tête.'],

        // Kettlebell & haltères
        ['kb-swing', 'Kettlebell swings', 'kettlebell', 'kettlebell', 'reps', 15, '24/16 kg', false, 'Swing américain : la kettlebell monte au-dessus de la tête.'],
        ['goblet-squat', 'Goblet squats', 'kettlebell', 'kettlebell', 'reps', 12, '24/16 kg', false, 'Squat en tenant la kettlebell contre la poitrine.'],
        ['db-snatch', 'Arrachés haltère', 'kettlebell', 'halteres', 'reps', 12, '22,5/15 kg', false, 'Alterner les bras à chaque répétition.'],
        ['db-thruster', 'Thrusters haltères', 'kettlebell', 'halteres', 'reps', 12, '2×15/10 kg', false, 'Squat puis développé avec deux haltères.'],
        ['devil-press', 'Devil press', 'kettlebell', 'halteres', 'reps', 8, '2×15/10 kg', false, 'Burpee sur les haltères puis double arraché.'],

        // Force & accessoires
        ['wall-ball', 'Wall balls', 'force', 'medecine_ball', 'reps', 15, '9/6 kg', false, 'Squat puis lancer contre une cible à 3 m.'],
        ['med-ball-clean', 'Medecine ball cleans', 'force', 'medecine_ball', 'reps', 15, '9/6 kg', false, 'Ramasser le ballon au sol et le réceptionner en squat.'],
        ['planche', 'Gainage planche', 'force', 'aucun', 's', 45, null, false, 'Corps aligné sur les avant-bras.'],
        ['hollow-hold', 'Hollow hold', 'force', 'aucun', 's', 30, null, false, 'Dos plaqué au sol, bras et jambes tendus décollés.'],
        ['chaise', 'Chaise contre le mur', 'force', 'aucun', 's', 45, null, false, 'Cuisses parallèles au sol, dos au mur.'],
    ],

    'wods' => [
        ['Cindy', 'amrap', 'moyen', 20, null, "Un benchmark incontournable.\nGardez un rythme régulier : visez un tour toutes les minutes environ.", [['pull-up', 5], ['push-up', 10], ['air-squat', 15]]],
        ['Helen', 'for_time', 'moyen', 15, 3, "Benchmark classique : 3 tours le plus vite possible.\nLa course sert de récupération active, ne la sacrifiez pas.", [['course', 400], ['kb-swing', 21], ['pull-up', 12]]],
        ['Grace', 'for_time', 'difficile', 10, 1, "30 épaulés-jetés pour un temps.\nSingles rapides plutôt que séries cassées.", [['clean-and-jerk', 30]]],
        ['Karen', 'for_time', 'moyen', 15, 1, "150 wall balls, c'est tout. Fractionnez tôt pour ne pas exploser.", [['wall-ball', 150]]],
        ['Le Réveil', 'emom', 'facile', 12, null, 'Idéal pour une reprise : qualité de mouvement avant tout.', [['air-squat', 12], ['push-up', 8], ['sit-up', 12]]],
        ['Poids du corps 360', 'amrap', 'facile', 15, null, 'Aucun matériel nécessaire : parfait à la maison ou en voyage.', [['burpee', 8], ['fentes', 16], ['mountain-climbers', 24], ['v-ups', 10]]],
        ['La Forge', 'for_time', 'difficile', 20, 5, 'Barre lourde, souffle court : gardez les thrusters sans pause si possible.', [['rameur', 12], ['thruster', 10], ['toes-to-bar', 10]]],
        ['Tabata Brûlant', 'tabata', 'moyen', 16, null, '4 mouvements, 8 intervalles chacun, 1 minute de repos entre les blocs.', [['air-squat', 8], ['push-up', 8], ['sit-up', 8], ['burpee', 8]]],
        ['Kettlebell Express', 'amrap', 'moyen', 12, null, 'Une seule kettlebell suffit. Hanches explosives sur les swings !', [['kb-swing', 15], ['goblet-squat', 12], ['burpee', 9]]],
        ['Escalier', 'emom', 'moyen', 16, null, "Alternez les 4 stations pendant 16 minutes. Restez sous 40 s d'effort.", [['assault-bike', 12], ['devil-press', 6], ['box-jump', 10], ['hollow-hold', 30]]],
        ['Chipper du Dimanche', 'for_time', 'difficile', 35, 1, "Un long chipper : chaque mouvement une seule fois, dans l'ordre.", [['course', 800], ['wall-ball', 50], ['pull-up', 40], ['kb-swing', 50], ['burpee', 30], ['course', 800]]],
        ['Gainage Total', 'emom', 'facile', 10, null, 'Séance courte orientée sangle abdominale.', [['planche', 40], ['v-ups', 12]]],
        ['Double Moteur', 'amrap', 'difficile', 20, null, 'Cardio et haltéro : changez de rythme sans vous arrêter.', [['double-unders', 50], ['power-clean', 8], ['chest-to-bar', 8]]],
        ['Sprint 8', 'for_time', 'facile', 8, 3, 'Court et intense : tout donner sur 3 tours.', [['jumping-jacks', 30], ['air-squat', 15], ['push-up', 10]]],
    ],
];
