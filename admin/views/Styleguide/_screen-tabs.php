<?php

styleguideExample('Record', static function () {
    ?>
    <form>
        <nav class="screen-tabs js-screen-tabs" role="tablist" data-hash="off">
            <button type="button" role="tab" class="screen-tabs__tab js-screen-tab" data-screen="record-details">
                Details
            </button>
            <button type="button" role="tab" class="screen-tabs__tab js-screen-tab" data-screen="record-media">
                Media
                <span class="screen-tabs__count">4</span>
            </button>
            <button type="button" role="tab" class="screen-tabs__tab js-screen-tab" data-screen="record-meta">
                Metadata
            </button>
            <button type="button" role="tab" class="screen-tabs__tab js-screen-tab" data-screen="record-publishing">
                Publishing
            </button>
        </nav>
        <div class="screen-tabs__panel js-screen-panel" data-screen="record-details">
            <?php
            styleguideField('Title', 'Quarterly report');
            styleguideField('Summary', 'A short summary of the item.');
            styleguideField('Author', 'Alex Rivera');
            styleguideSaveBar();
            ?>
        </div>
        <div class="screen-tabs__panel js-screen-panel" data-screen="record-media">
            <p>Four assets attached.</p>
            <?php styleguideSaveBar(); ?>
        </div>
        <div class="screen-tabs__panel js-screen-panel" data-screen="record-meta">
            <?php
            styleguideField('Meta title', 'Quarterly report');
            styleguideField('Slug', '/reports/quarterly');
            styleguideSaveBar();
            ?>
        </div>
        <div class="screen-tabs__panel js-screen-panel" data-screen="record-publishing">
            <?php
            styleguideField('Status', 'Draft');
            styleguideField('Publish at', '24 Sep 2026, 09:00');
            styleguideSaveBar();
            ?>
        </div>
    </form>
    <?php
});

styleguideExample('Defaults to first error', static function () {
    ?>
    <form>
        <nav class="screen-tabs js-screen-tabs" role="tablist" data-hash="off">
            <button type="button" role="tab" class="screen-tabs__tab js-screen-tab" data-screen="event-overview">
                Overview
            </button>
            <button type="button" role="tab" class="screen-tabs__tab js-screen-tab" data-screen="event-venue">
                Location
            </button>
            <button type="button" role="tab" class="screen-tabs__tab js-screen-tab" data-screen="event-tickets">
                Tickets
            </button>
        </nav>
        <div class="screen-tabs__panel js-screen-panel" data-screen="event-overview">
            <?php
            styleguideField('Name', 'Team offsite');
            styleguideSaveBar();
            ?>
        </div>
        <div class="screen-tabs__panel js-screen-panel" data-screen="event-venue">
            <?php
            styleguideField('Location', '12 High Street');
            styleguideSaveBar();
            ?>
        </div>
        <div class="screen-tabs__panel js-screen-panel" data-screen="event-tickets">
            <?php
            styleguideField('Capacity', '', ['error' => true]);
            styleguideSaveBar();
            ?>
        </div>
    </form>
    <?php
});

styleguideExample('Stacked', static function () {
    $inner = static function (string $group, string $title) {
        styleguideTabs($group, [
            'tab-general' => [
                'label'   => 'General',
                'content' => static function () use ($title) {
                    styleguideField('Title', $title);
                },
            ],
            'tab-media' => ['label' => 'Media', 'content' => '<p>Images and caption.</p>'],
            'tab-meta'  => ['label' => 'Metadata', 'content' => '<p>Title, description and slug.</p>'],
        ]);
    };
    ?>
    <form>
        <nav class="screen-tabs js-screen-tabs" role="tablist" data-hash="off">
            <button type="button" role="tab" class="screen-tabs__tab js-screen-tab" data-screen="stack-record">
                Record
            </button>
            <button type="button" role="tab" class="screen-tabs__tab js-screen-tab" data-screen="stack-related">
                Related
                <span class="screen-tabs__count">6</span>
            </button>
        </nav>
        <div class="screen-tabs__panel js-screen-panel" data-screen="stack-record">
            <?php
            styleguideTabs(
                'sg-stack-langs',
                [
                    'tab-en' => [
                        'label'   => 'English',
                        'content' => static function () use ($inner) {
                            $inner('sg-stack-en', 'North region overview');
                        },
                    ],
                    'tab-de' => [
                        'label'   => 'Deutsch',
                        'content' => static function () use ($inner) {
                            $inner('sg-stack-de', 'Überblick Nordregion');
                        },
                    ],
                    'tab-fr' => [
                        'label'   => 'Français',
                        'content' => static function () use ($inner) {
                            $inner('sg-stack-fr', 'Aperçu région nord');
                        },
                    ],
                ],
                ['segmented' => true]
            );
            styleguideSaveBar();
            ?>
        </div>
        <div class="screen-tabs__panel js-screen-panel screen-tabs__panel--no-save" data-screen="stack-related">
            <table class="table table-striped styleguide-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Status</th>
                        <th>Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Home</td>
                        <td>Published</td>
                        <td>18 Sep 2026</td>
                    </tr>
                    <tr>
                        <td>About</td>
                        <td>Published</td>
                        <td>12 Sep 2026</td>
                    </tr>
                    <tr>
                        <td>Team</td>
                        <td>Draft</td>
                        <td>21 Sep 2026</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </form>
    <?php
});

$aWorkspaces = [
    'Overview',
    'Body copy',
    'Media library',
    'Related items',
    'Metadata',
    'Attachments',
    'Access',
    'History',
    'Translations',
    'Notifications',
    'Integrations',
    'Advanced',
];

styleguideExample('Overflow', static function () use ($aWorkspaces) {
    ?>
    <form>
        <nav class="screen-tabs js-screen-tabs" role="tablist" data-hash="off">
            <?php
            foreach ($aWorkspaces as $i => $label) {
                $slug = 'ws-' . ($i + 1);
                ?>
                <button type="button" role="tab" class="screen-tabs__tab js-screen-tab" data-screen="<?=$slug?>">
                    <?=$label?>
                    <?php
                    if ($label === 'Media library') {
                        echo '<span class="screen-tabs__count">4</span>';
                    }
                    ?>
                </button>
                <?php
            }
            ?>
        </nav>
        <?php
        foreach ($aWorkspaces as $i => $label) {
            $slug = 'ws-' . ($i + 1);
            ?>
            <div class="screen-tabs__panel js-screen-panel" data-screen="<?=$slug?>">
                <p><?=$label?>.</p>
                <?php styleguideSaveBar(); ?>
            </div>
            <?php
        }
        ?>
    </form>
    <?php
});
