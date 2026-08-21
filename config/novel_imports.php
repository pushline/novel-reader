<?php

return [
    'stories' => [
        'overgeared' => [
            'title' => 'Overgeared',
            'jobs' => [
                [
                    'label' => 'chapters 1-2059 from NovelFull',
                    'command' => 'novels:import-from-url-pattern',
                    'options' => [
                        '--story-slug' => 'overgeared',
                        '--title' => 'Overgeared',
                        '--url-pattern' => 'https://novelfull.com/overgeared/chapter-{chapter}.html',
                        '--start' => 1,
                        '--end' => 2059,
                    ],
                ],
            ],
        ],
        'the-reincarnated-assassin-is-a-genius-swordsman' => [
            'title' => 'The Reincarnated Assassin Is a Genius Swordsman',
            'jobs' => [
                [
                    'label' => 'chapters 1-599 from NovelLunar',
                    'command' => 'novels:import-from-url-pattern',
                    'options' => [
                        '--story-slug' => 'the-reincarnated-assassin-is-a-genius-swordsman',
                        '--title' => 'The Reincarnated Assassin Is a Genius Swordsman',
                        '--url-pattern' => 'https://novellunar.com/novel/the-reincarnated-assassin-is-a-genius-swordsman/chapter/{chapter}',
                        '--start' => 1,
                        '--end' => 599,
                    ],
                ],
                [
                    'label' => 'chapters 600-1506 from NovelTranslation',
                    'command' => 'novels:import-from-url-pattern',
                    'options' => [
                        '--story-slug' => 'the-reincarnated-assassin-is-a-genius-swordsman',
                        '--url-pattern' => 'https://noveltranslation.net/novel/10/chapter/{chapter}.0',
                        '--start' => 600,
                        '--end' => 1506,
                    ],
                ],
            ],
        ],
        'eternally-regressing-knight' => [
            'title' => 'Eternally Regressing Knight',
            'jobs' => [
                [
                    'label' => 'chapters 1-730 from RevengerNovel',
                    'command' => 'novels:import-from-chapter-chain',
                    'options' => [
                        '--story-slug' => 'eternally-regressing-knight',
                        '--title' => 'Eternally Regressing Knight',
                        '--start-url' => 'https://revengernovel.com/series/a-knight-who-eternally-regresses/54/chapter-1',
                        '--end' => 730,
                    ],
                ],
                [
                    'label' => 'chapters 731-917 from WebNovel',
                    'command' => 'novels:import-from-chapter-chain',
                    'options' => [
                        '--story-slug' => 'eternally-regressing-knight',
                        '--start-url' => 'https://en.webnovel.com/book/33789555708924705/92812975580183896',
                        '--end' => 917,
                    ],
                ],
            ],
        ],
    ],
];
