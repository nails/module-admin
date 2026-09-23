<?php

$aSimple = [
    'tab-one'   => ['label' => 'Tab 1', 'content' => '<p>Tab panel one.</p>'],
    'tab-two'   => ['label' => 'Tab 2', 'content' => '<p>Tab panel two.</p>'],
    'tab-three' => ['label' => 'Tab 3', 'content' => '<p>Tab panel three.</p>'],
];

styleguideExample('Independent', static function () use ($aSimple) {
    styleguideTabs('sg-tabs-independent', $aSimple);
});

styleguideExample('Nested', static function () use ($aSimple) {
    styleguideTabs('sg-tabs-nested-outer', [
        'tab-one' => [
            'label'   => 'Tab 1',
            'content' => static function () use ($aSimple) {
                styleguideTabs('sg-tabs-nested-inner', $aSimple);
            },
        ],
        'tab-two'   => ['label' => 'Tab 2', 'content' => '<p>Tab panel two.</p>'],
        'tab-three' => ['label' => 'Tab 3', 'content' => '<p>Tab panel three.</p>'],
    ]);
});

styleguideExample('Linked', static function () use ($aSimple) {
    styleguideTabs('sg-tabs-mimic', $aSimple);
    styleguideTabs('sg-tabs-mimic', $aSimple);
});

styleguideExample('Defaults to tab 2', static function () use ($aSimple) {
    styleguideTabs('sg-tabs-default', $aSimple, ['default' => 'tab-two']);
});

styleguideExample('Defaults to first error', static function () {
    styleguideTabs('sg-tabs-error', [
        'tab-one'   => ['label' => 'Tab 1', 'content' => '<p>Tab panel one.</p>'],
        'tab-two'   => ['label' => 'Tab 2', 'content' => '<p class="alert alert-danger">Tab panel two.</p>'],
        'tab-three' => ['label' => 'Tab 3', 'content' => '<p class="alert alert-danger">Tab panel three.</p>'],
    ]);
});

styleguideExample('Segmented', static function () {
    styleguideTabs(
        'sg-tabs-seg-langs',
        [
            'tab-en' => ['label' => 'English', 'content' => '<p>English copy.</p>'],
            'tab-de' => ['label' => 'Deutsch', 'content' => '<p>Deutsche Fassung.</p>'],
            'tab-fr' => ['label' => 'Français', 'content' => '<p>Version française.</p>'],
        ],
        ['segmented' => true]
    );
});

styleguideExample('Segmented with sections', static function () {
    $inner = static function (string $group, array $tabs, array $opts = []) {
        return static function () use ($group, $tabs, $opts) {
            styleguideTabs($group, $tabs, $opts);
        };
    };

    styleguideTabs(
        'sg-tabs-seg-nested',
        [
            'tab-en' => [
                'label'   => 'English',
                'content' => $inner('sg-tabs-seg-nested-en', [
                    'tab-general' => [
                        'label'   => 'General',
                        'content' => static function () {
                            styleguideField('Title', 'Quarterly report');
                            echo '<p>English summary for the north region.</p>';
                        },
                    ],
                    'tab-media' => ['label' => 'Media', 'content' => '<p>Cover: report-cover.jpg</p>'],
                    'tab-meta'  => ['label' => 'Metadata', 'content' => '<p>Slug: /reports/quarterly</p>'],
                ]),
            ],
            'tab-de' => [
                'label'   => 'Deutsch',
                'content' => $inner(
                    'sg-tabs-seg-nested-de',
                    [
                        'tab-general' => [
                            'label'   => 'Allgemein',
                            'content' => static function () {
                                styleguideField('Titel', 'Quartalsbericht');
                                echo '<p>Deutsche Zusammenfassung für die Nordregion.</p>';
                            },
                        ],
                        'tab-media' => ['label' => 'Medien', 'content' => '<p>Titelbild: bericht-titel.jpg</p>'],
                        'tab-meta'  => ['label' => 'Metadaten', 'content' => '<p>Slug: /berichte/quartal</p>'],
                    ],
                    ['default' => 'tab-media']
                ),
            ],
            'tab-fr' => [
                'label'   => 'Français',
                'content' => $inner(
                    'sg-tabs-seg-nested-fr',
                    [
                        'tab-general' => [
                            'label'   => 'Général',
                            'content' => static function () {
                                styleguideField('Titre', 'Rapport trimestriel');
                                echo '<p>Résumé français pour la région nord.</p>';
                            },
                        ],
                        'tab-media' => ['label' => 'Médias', 'content' => '<p>Couverture : rapport-couv.jpg</p>'],
                        'tab-meta'  => ['label' => 'Métadonnées', 'content' => '<p>Slug : /rapports/trimestriel</p>'],
                    ],
                    ['default' => 'tab-meta']
                ),
            ],
        ],
        ['segmented' => true]
    );
});

styleguideExample('Sections with segmented', static function () {
    styleguideTabs('sg-tabs-outer-then-seg', [
        'tab-content' => [
            'label'   => 'Content',
            'content' => static function () {
                styleguideTabs(
                    'sg-tabs-inner-seg',
                    [
                        'tab-draft'     => ['label' => 'Draft', 'content' => '<p>Working copy.</p>'],
                        'tab-review'    => ['label' => 'In review', 'content' => '<p>Waiting on sign-off.</p>'],
                        'tab-published' => ['label' => 'Published', 'content' => '<p>Live on the public site.</p>'],
                    ],
                    ['segmented' => true]
                );
            },
        ],
        'tab-settings' => ['label' => 'Settings', 'content' => '<p>Section settings.</p>'],
        'tab-history'  => ['label' => 'History', 'content' => '<p>Revision history.</p>'],
    ]);
});

$aOverflow = [
    'tab-details'      => ['label' => 'Details', 'content' => '<p>Title, summary and ownership.</p>'],
    'tab-content'      => ['label' => 'Body copy', 'content' => '<p>Main copy for this record.</p>'],
    'tab-media'        => ['label' => 'Media', 'content' => '<p>Images and attachments.</p>'],
    'tab-related'      => ['label' => 'Related items', 'content' => '<p>Linked records.</p>'],
    'tab-metadata'     => ['label' => 'Metadata', 'content' => '<p>Title, description and slug.</p>'],
    'tab-attachments'  => ['label' => 'Attachments', 'content' => '<p>Uploaded files.</p>'],
    'tab-access'       => ['label' => 'Access', 'content' => '<p>Who can edit this record.</p>'],
    'tab-translations' => ['label' => 'Translations', 'content' => '<p>Localisation status.</p>'],
    'tab-history'      => ['label' => 'History', 'content' => '<p>Previous revisions.</p>'],
    'tab-notes'        => ['label' => 'Notes', 'content' => '<p>Internal editor notes.</p>'],
    'tab-notifications'=> ['label' => 'Notifications', 'content' => '<p>Alerts for this record.</p>'],
    'tab-integrations' => ['label' => 'Integrations', 'content' => '<p>Third-party connections.</p>'],
    'tab-schedule'     => ['label' => 'Schedule', 'content' => '<p>Publish date and time.</p>'],
    'tab-permissions'  => ['label' => 'Permissions', 'content' => '<p>Who can publish.</p>'],
    'tab-publishing'   => ['label' => 'Publishing', 'content' => '<p>Status and visibility.</p>'],
    'tab-advanced'     => ['label' => 'Advanced', 'content' => '<p>Less common options.</p>'],
];

styleguideExample('Overflow', static function () use ($aOverflow) {
    styleguideTabs('sg-tabs-overflow', $aOverflow);
});

styleguideExample('Overflow, nested', static function () use ($aOverflow) {
    styleguideTabs(
        'sg-tabs-seg-nested-overflow',
        [
            'tab-en' => [
                'label'   => 'English',
                'content' => static function () use ($aOverflow) {
                    styleguideTabs('sg-tabs-seg-nested-overflow-en', $aOverflow);
                },
            ],
            'tab-de' => ['label' => 'Deutsch', 'content' => '<p>Deutsche Fassung.</p>'],
            'tab-fr' => ['label' => 'Français', 'content' => '<p>Version française.</p>'],
            'tab-es' => ['label' => 'Español', 'content' => '<p>Versión en español.</p>'],
            'tab-it' => ['label' => 'Italiano', 'content' => '<p>Versione italiana.</p>'],
            'tab-pt' => ['label' => 'Português', 'content' => '<p>Versão em português.</p>'],
            'tab-nl' => ['label' => 'Nederlands', 'content' => '<p>Nederlandse versie.</p>'],
            'tab-sv' => ['label' => 'Svenska', 'content' => '<p>Svensk version.</p>'],
            'tab-da' => ['label' => 'Dansk', 'content' => '<p>Dansk version.</p>'],
            'tab-ja' => ['label' => '日本語', 'content' => '<p>日本語版。</p>'],
        ],
        ['segmented' => true]
    );
});
