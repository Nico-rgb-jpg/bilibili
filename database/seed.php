<?php
/**
 * Données de démonstration : séries originales (inventées pour le projet),
 * épisodes, créneaux de planning et deux comptes de test.
 */

return [
    'series' => [
        [
            'titre' => 'Les Lames du Crépuscule',
            'genre' => 'Action', 'annee' => 2025, 'statut' => 'En cours', 'teinte' => 352,
            'synopsis' => "Dans une cité fortifiée où le soleil ne se couche jamais, Kaito, apprenti forgeron, découvre que chaque lame qu'il forge garde la mémoire de son ancien porteur. Quand une épée brisée lui révèle un complot contre la reine, il doit choisir entre son atelier et la vérité.",
            'saisons' => [
                ['La forge silencieuse', 'Mémoire d\'acier', 'Le tournoi des cendres', 'Ce que cache la garde', 'Lame contre lame', 'Le crépuscule promis'],
                ['Retour à la cité', 'Les forgerons de l\'Est', 'Une lame sans nom', 'La reine au masque', 'Braises', 'Aube rouge'],
            ],
            'planning' => [[3, '21:00'], [6, '18:30']],
        ],
        [
            'titre' => 'Orbite Zéro',
            'genre' => 'Science-fiction', 'annee' => 2024, 'statut' => 'En cours', 'teinte' => 205,
            'synopsis' => "L'équipage de la station de ravitaillement Halcyon, en orbite autour de Mars, capte un signal qui répète leurs propres voix… avec trois jours d'avance. Cinq techniciens, un ordinateur de bord trop poli et une question : peut-on changer ce qu'on a déjà entendu ?",
            'saisons' => [
                ['Signal fantôme', 'Trois jours d\'avance', 'Dépressurisation', 'La voix de Nadia', 'Fenêtre de tir', 'Point de Lagrange'],
                ['Après l\'écho', 'Le second équipage', 'Gravité', 'Boucle ouverte', 'Rentrée atmosphérique'],
            ],
            'planning' => [[2, '20:00']],
        ],
        [
            'titre' => 'Le Café des Chats Perdus',
            'genre' => 'Comédie', 'annee' => 2023, 'statut' => 'Terminée', 'teinte' => 28,
            'synopsis' => "Mio reprend le petit café de sa grand-mère dans une ruelle de Kyoto. Problème : les sept chats qui y vivent ne parlent qu'après minuit, et ils ont des avis très arrêtés sur la carte, les clients et la vie sentimentale de Mio.",
            'saisons' => [
                ['L\'héritage à moustaches', 'Minuit pile', 'Le client du mardi', 'Matcha et malentendus', 'Le chat qui ne revenait pas', 'Fermeture exceptionnelle', 'Sept bols de lait'],
            ],
            'planning' => [],
        ],
        [
            'titre' => 'Sous la Neige de Hokkai',
            'genre' => 'Drame', 'annee' => 2022, 'statut' => 'Terminée', 'teinte' => 200,
            'synopsis' => "Après dix ans d'absence, Haru revient dans son village du nord pour vendre la maison familiale. Un hiver, une vieille radio et les lettres jamais envoyées de son père vont l'obliger à rester plus longtemps que prévu.",
            'saisons' => [
                ['Le dernier train', 'La maison froide', 'Fréquence 88.4', 'Lettres sous le plancher', 'Tempête', 'Ce qui fond au printemps'],
            ],
            'planning' => [],
        ],
        [
            'titre' => 'Club de Volley de Minato',
            'genre' => 'Sport', 'annee' => 2025, 'statut' => 'En cours', 'teinte' => 22,
            'synopsis' => "Le club de volley du lycée Minato compte quatre membres, un gymnase qui fuit et une capitaine qui refuse d'abandonner. Objectif : rejoindre le tournoi régional avant que le club ne soit dissous.",
            'saisons' => [
                ['Quatre, c\'est pas assez', 'Le passeur venu d\'ailleurs', 'Premier set', 'Réception ratée', 'Le gymnase sous la pluie', 'Match d\'entraînement'],
                ['Les qualifications', 'Smash', 'Temps mort', 'Le mur adverse', 'Balle de match'],
            ],
            'planning' => [[1, '19:00'], [5, '19:00']],
        ],
        [
            'titre' => 'Le Jardin des Horloges',
            'genre' => 'Fantastique', 'annee' => 2024, 'statut' => 'En cours', 'teinte' => 158,
            'synopsis' => "Derrière la boutique d'un horloger se cache un jardin où chaque fleur mesure le temps qu'il reste à quelqu'un. Lina, la nouvelle apprentie, découvre qu'une des fleurs porte le nom de sa meilleure amie.",
            'saisons' => [
                ['L\'apprentie', 'Les fleurs-sabliers', 'Remonter le temps', 'L\'horloger absent', 'Une heure de trop', 'Le jardin d\'hiver'],
            ],
            'planning' => [[4, '20:30']],
        ],
        [
            'titre' => 'Néon Requiem',
            'genre' => 'Mystère', 'annee' => 2026, 'statut' => 'En cours', 'teinte' => 288,
            'synopsis' => "Dans une mégapole où les souvenirs se revendent au marché noir, l'inspectrice Ren enquête sur des disparitions dont personne ne se souvient… sauf elle. Chaque indice efface un peu plus sa propre mémoire.",
            'saisons' => [
                ['Mémoire vive', 'Le marchand d\'oublis', 'Pluie acide', 'Le témoin vide', 'Fragmentation', 'Requiem'],
            ],
            'planning' => [[5, '22:00'], [7, '21:00']],
        ],
        [
            'titre' => 'Lettres à l\'Été',
            'genre' => 'Romance', 'annee' => 2023, 'statut' => 'Terminée', 'teinte' => 335,
            'synopsis' => "Deux lycéens que tout oppose échangent des lettres anonymes via une boîte oubliée au bord de la mer. Ils se croisent chaque jour sans le savoir, jusqu'au festival de fin d'été.",
            'saisons' => [
                ['La boîte bleue', 'Écriture inconnue', 'Le phare', 'Feux d\'artifice', 'Réponse en retard', 'Fin d\'été'],
            ],
            'planning' => [],
        ],
        [
            'titre' => 'L\'Archiviste de Valmorre',
            'genre' => 'Fantastique', 'annee' => 2021, 'statut' => 'Terminée', 'teinte' => 42,
            'synopsis' => "Ilyas est chargé de recopier les archives d'un royaume en ruine. Mais certains livres se réécrivent la nuit, et l'histoire officielle ne correspond plus du tout à ce qu'il a vu de ses propres yeux.",
            'saisons' => [
                ['Encre et poussière', 'Le livre qui change', 'Les copistes', 'La bibliothèque engloutie', 'Le roi effacé', 'Dernière page'],
                ['Valmorre libre', 'Le conseil des scribes', 'Palimpseste', 'Le chant des archives', 'Épilogue'],
            ],
            'planning' => [],
        ],
        [
            'titre' => 'Brigade Kitsune',
            'genre' => 'Action', 'annee' => 2026, 'statut' => 'En cours', 'teinte' => 12,
            'synopsis' => "Une unité de police secrète traque les esprits renards qui se cachent parmi les humains à Osaka. Sa meilleure recrue ? Une kitsune qui a décidé de changer de camp, au grand désespoir de son partenaire.",
            'saisons' => [
                ['Neuf queues', 'Partenaires forcés', 'Le marché de nuit', 'Illusions', 'Traque à Dōtonbori', 'Le renard blanc'],
            ],
            'planning' => [[6, '21:30']],
        ],
        [
            'titre' => 'Les Enquêtes de Sora',
            'genre' => 'Mystère', 'annee' => 2024, 'statut' => 'Terminée', 'teinte' => 245,
            'synopsis' => "Sora, douze ans, tient un club de détectives dans le grenier de l'école. Vols de goûters, fantôme du gymnase, carnet disparu : aucune énigme n'est trop petite, surtout quand elles commencent à se relier entre elles.",
            'saisons' => [
                ['Le club du grenier', 'Le goûter disparu', 'Le fantôme du gymnase', 'Code secret', 'Le carnet rouge', 'L\'énigme finale'],
            ],
            'planning' => [],
        ],
        [
            'titre' => 'Cuisine de Combat',
            'genre' => 'Comédie', 'annee' => 2026, 'statut' => 'En cours', 'teinte' => 95,
            'synopsis' => "Dans une école de cuisine où les examens se règlent en duels télévisés, Taro, qui ne sait faire que des onigiris, doit survivre à une année d'affrontements culinaires absurdes.",
            'saisons' => [
                ['Le riz de la discorde', 'Duel au wok', 'La sauce secrète', 'Ramen à minuit', 'Le jury impitoyable'],
            ],
            'planning' => [[3, '18:00'], [7, '12:00']],
        ],
    ],

    'utilisateurs' => [
        ['pseudo' => 'admin', 'email' => 'admin@bilibili.local', 'mot_de_passe' => 'Admin1234', 'role' => 'admin'],
        ['pseudo' => 'demo',  'email' => 'demo@bilibili.local',  'mot_de_passe' => 'Demo1234',  'role' => 'membre'],
    ],

    // Activité du compte de démo, pour que l'accueil affiche « Continuer à regarder » et « Ma liste ».
    'demo' => [
        'favoris' => ['orbite-zero', 'le-jardin-des-horloges', 'neon-requiem'],
        'vus' => [
            ['orbite-zero', 1, 3],            // série, saison, épisodes vus jusqu'à
            ['les-lames-du-crepuscule', 1, 2],
        ],
    ],
];
