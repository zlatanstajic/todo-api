<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Supported Locales
    |--------------------------------------------------------------------------
    |
    | Locales served by the API and scanned by the timelines:import command
    | (one sub-folder per locale inside the histories directory).
    |
    */

    'locales' => ['en', 'sr'],

    'default_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Section Markers
    |--------------------------------------------------------------------------
    |
    | Section markers per locale, ported verbatim from the frontend parser
    | (src/lib/parse.ts MARKER_SETS in the alternate-history-wiki repo).
    | The "all" list contains markers that terminate a paragraph section.
    |
    */

    'markers' => [
        'en' => [
            'part_one' => 'PART ONE: RECORDED HISTORY',
            'part_two' => 'PART TWO: ALTERNATE HISTORY',
            'figures' => 'NOTABLE FIGURES',
            'tldr' => 'TL;DR',
            'all' => [
                'PART ONE: RECORDED HISTORY',
                'PART TWO: ALTERNATE HISTORY',
                'NOTABLE FIGURES',
            ],
        ],
        'sr' => [
            'part_one' => 'PRVI DEO: ZAPISANA ISTORIJA',
            'part_two' => 'DRUGI DEO: ALTERNATIVNA ISTORIJA',
            'figures' => 'ZNAČAJNE LIČNOSTI',
            'tldr' => 'TL;DR',
            'all' => [
                'PRVI DEO: ZAPISANA ISTORIJA',
                'DRUGI DEO: ALTERNATIVNA ISTORIJA',
                'ZNAČAJNE LIČNOSTI',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Translation Pairs
    |--------------------------------------------------------------------------
    |
    | EN slug to SR slug map, ported from the frontend timelinePairs map
    | (src/lib/seo.ts in the alternate-history-wiki repo). Adding a timeline
    | to the wiki repo requires updating this map by hand.
    |
    */

    'pairs' => [
        'aleksandar-obrenovic-survives-may-coup-of-1903' => 'aleksandar-obrenovic-prezivljava-majski-puc-1903',
        'alternate-nuclear-destruction-of-cuban-crisis' => 'alternativno-nuklearno-unitenje-kubanske-krize',
        'american-revolution-fails' => 'americka-revolucija-ne-uspeva',
        'arkan-was-never-assassinated' => 'arkan-nikada-nije-ubijen',
        'balkans-that-developed-without-ottoman-occupation' => 'balkan-koji-se-razvio-bez-osmanske-okupacije',
        'divergent-soviet-moon-landing-chronicle' => 'divergentna-hronika-sovjetskog-sletanja-na-mesec',
        'franz-ferdinand-was-never-assassinated-in-sarajevo' => 'franc-ferdinand-nikada-nije-ubijen-u-sarajevu',
        'ivan-stambolic-was-never-murdered' => 'ivan-stambolic-nikada-nije-ubijen',
        'karadjordje-survives-assassination' => 'karadjordje-prezivljava-atentat',
        'milos-obrenovic-and-serbian-autonomy' => 'milos-obrenovic-i-srpska-autonomija',
        'nikola-tesla-lost-the-war-of-the-currents-to-thomas-edison' => 'nikola-tesla-izgubio-rat-struja-protiv-tomasa-edisona',
        'nikola-tesla-never-emigrated-to-the-united-states' => 'nikola-tesla-nikada-nije-emigrirao-u-sjedinjene-drzave',
        'nikola-tesla-was-never-allowed-a-formal-education' => 'nikoli-tesli-nikada-nije-dozvoljeno-formalno-obrazovanje',
        'no-nato-bombing-of-yugoslavia' => 'nema-natovog-bombardovanja-jugoslavije',
        'no-unification-to-the-kingdom-of-shs' => 'bez-ujedinjenja-u-kraljevinu-shs',
        'peaceful-breakup-of-yugoslavia' => 'miran-raspad-jugoslavije',
        'petrov-crisis-starts-nuclear-war' => 'petrovljeva-kriza-zapocinje-nuklearni-rat',
        'slobodan-milosevic-and-the-1996-1997-serbian-protests' => 'slobodan-milosevic-i-srpski-protesti-1996-1997',
        'slobodan-milosevic-never-rose-to-power' => 'slobodan-milosevic-nikada-nije-dosao-na-vlast',
        'the-ottoman-conquest-of-the-balkans-fails' => 'osmansko-osvajanje-balkana-propada',
        'tito-assassinated-after-stalin-split' => 'tito-ubijen-nakon-raskola-sa-staljinom',
        'vuk-karadzic-s-language-reform-never-succeeded' => 'jezicka-reforma-vuka-karadzica-nikada-nije-uspela',
        'what-if-king-alexander-i-survived-marseille' => 'kralj-aleksandar-je-preziveo-marsej',
        'what-if-korea-never-split-a-unified-korean-nation' => 'sta-da-se-koreja-nikada-nije-podelila-ujedinjena-korejska-nacija',
        'zoran-djindjic-survives-assassination' => 'zoran-djindjic-prezivljava-atentat',
    ],

];
