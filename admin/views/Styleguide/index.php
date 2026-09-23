<?php

include __DIR__ . '/_helpers.php';

$aSections = [
    'typography'  => 'Typography',
    'buttons'     => 'Buttons',
    'badges'      => 'Badges',
    'alerts'      => 'Alerts',
    'tables'      => 'Tables',
    'fieldsets'   => 'Fieldsets',
    'fields'      => 'Form fields',
    'tabs'        => 'Tabs',
    'screen-tabs' => 'Screen Tabs',
];

?>
<div class="group-admin group-admin-styleguide">
    <nav class="styleguide-nav" aria-label="On this page">
        <ul class="styleguide-nav__list">
            <?php
            $bFirst = true;
            foreach ($aSections as $sId => $sLabel) {
                ?>
                <li>
                    <a class="styleguide-nav__link<?=$bFirst ? ' is-active' : ''?>" href="#<?=$sId?>">
                        <?=$sLabel?>
                    </a>
                </li>
                <?php
                $bFirst = false;
            }
            ?>
        </ul>
    </nav>

    <div class="styleguide-main">

        <section id="typography" class="styleguide-section">
            <?php styleguideSectionHeader('Typography'); ?>
            <?php
            styleguideExample(null, static function () {
                ?>
                <h1>This is a &lt;h1&gt; heading</h1>
                <h2>This is a &lt;h2&gt; heading</h2>
                <h3>This is a &lt;h3&gt; heading</h3>
                <h4>This is a &lt;h4&gt; heading</h4>
                <h5>This is a &lt;h5&gt; heading</h5>
                <h6>This is a &lt;h6&gt; heading</h6>
                <p>
                    This is some body text. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                    tempor incididunt ut labore et dolore magna aliqua.
                </p>
                <p><small>This is some small text</small></p>
                <p><strong>This is some strong text</strong></p>
                <p><em>This is some italic text</em></p>
                <?php
            });
            ?>
        </section>

        <section id="buttons" class="styleguide-section">
            <?php styleguideSectionHeader('Buttons'); ?>
            <?php
            styleguideExample(null, static function () {
                $aSizes = ['btn-xs', 'btn-sm', 'btn-md', '', 'btn-lg'];
                $aTypes = [
                    '',
                    'btn-primary',
                    'btn-secondary',
                    'btn-brand-primary',
                    'btn-brand-secondary',
                    'btn-success',
                    'btn-danger',
                    'btn-info',
                    'btn-warning',
                    'btn-link',
                ];

                foreach ($aSizes as $sSize) {
                    echo '<p><code>' . ($sSize ?: 'btn') . '</code></p>';
                    echo '<p>';
                    foreach ($aTypes as $sType) {
                        ?>
                        <button class="btn <?=$sType?> <?=$sSize?>">
                            <?php
                            echo '.';
                            echo implode('.', array_filter([
                                'btn',
                                $sType,
                            ]));
                            ?>
                        </button>
                        <?php
                    }
                    echo '</p>';
                }
            });
            ?>
        </section>

        <section id="badges" class="styleguide-section">
            <?php styleguideSectionHeader('Badges'); ?>
            <?php
            styleguideExample(null, static function () {
                $aTypes = [
                    '',
                    'badge-primary',
                    'badge-secondary',
                    'badge-success',
                    'badge-danger',
                    'badge-info',
                    'badge-warning',
                ];

                foreach ($aTypes as $sType) {
                    ?>
                    <span class="badge <?=$sType?>">
                        <?php
                        echo '.';
                        echo implode('.', array_filter([
                            'badge',
                            $sType,
                        ]));
                        ?>
                    </span>
                    <?php
                }
            });
            ?>
        </section>

        <section id="alerts" class="styleguide-section">
            <?php styleguideSectionHeader('Alerts'); ?>
            <?php
            styleguideExample(null, static function () {
                $aTypes = [
                    '',
                    'alert-success',
                    'alert-danger',
                    'alert-info',
                    'alert-warning',
                ];

                foreach ($aTypes as $sType) {
                    ?>
                    <p class="alert <?=$sType?>">
                        <span class="alert__close">&times;</span>
                        <?php
                        echo '.';
                        echo implode('.', array_filter([
                            'alert',
                            $sType,
                        ]));
                        ?>
                    </p>
                    <?php
                }
            });
            ?>
        </section>

        <section id="tables" class="styleguide-section">
            <?php styleguideSectionHeader('Tables'); ?>
            <?php
            styleguideExample(null, static function () {
                ?>
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Column 1</th>
                            <th>Column 2</th>
                            <th>Column 3</th>
                            <th>Column 4</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Column 1</td>
                            <td>Column 2</td>
                            <td>Column 3</td>
                            <td>Column 4</td>
                        </tr>
                        <tr>
                            <td>Column 1</td>
                            <td>Column 2</td>
                            <td>Column 3</td>
                            <td>Column 4</td>
                        </tr>
                    </tbody>
                </table>
                <?php
            });
            ?>
        </section>

        <section id="fieldsets" class="styleguide-section">
            <?php
            styleguideSectionHeader(
                'Fieldsets',
                'The card that groups fields on an edit form. Nested fieldsets inset so they read as part of the parent. <code>data-collapse</code> turns the legend into a toggle; a fieldset that contains errors opens even if it was asked to start closed.'
            );
            ?>
            <?php
            styleguideExample('Top-level', static function () {
                ?>
                <fieldset>
                    <legend>I Am Legend</legend>
                    <p>
                        Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                        tempor incididunt ut labore et dolore magna aliqua.
                    </p>
                </fieldset>
                <?php
            });

            styleguideExample('Nested', static function () {
                ?>
                <fieldset>
                    <legend>I Am Legend</legend>
                    <p>
                        Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                        tempor incididunt ut labore et dolore magna aliqua.
                    </p>
                    <fieldset>
                        <legend>I Am Legend</legend>
                        <p>
                            Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                            tempor incididunt ut labore et dolore magna aliqua.
                        </p>
                    </fieldset>
                </fieldset>
                <?php
            });

            styleguideExample('Collapsible', static function () {
                ?>
                <fieldset data-collapse="open">
                    <legend>Initially open</legend>
                    <p>
                        Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                        tempor incididunt ut labore et dolore magna aliqua.
                    </p>
                </fieldset>
                <fieldset data-collapse="closed">
                    <legend>Initially closed</legend>
                    <p>
                        Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                        tempor incididunt ut labore et dolore magna aliqua.
                    </p>
                </fieldset>
                <fieldset data-collapse="closed">
                    <legend>Contains errors</legend>
                    <p class="alert alert-danger">
                        Open by default, despite requesting closed state.
                    </p>
                </fieldset>
                <?php
            });
            ?>
        </section>

        <section id="fields" class="styleguide-section">
            <?php
            styleguideSectionHeader(
                'Form fields',
                'Rendered with the real <code>form_field()</code> helpers — same markup and admin CSS as a normal edit form, including tips, readonly, and errors. Do not restyle these in the styleguide.'
            );
            ?>
            <?php include __DIR__ . '/_fields.php'; ?>
        </section>

        <section id="tabs" class="styleguide-section">
            <?php
            styleguideSectionHeader(
                'Tabs',
                'Default underline tabs join a card panel. <code>tabs--segmented</code> is a pill switcher whose panels carry no card, so a nested underline group can sit inside. Bars that share <code>data-tabgroup</code> stay in sync. When labels overflow, the bar scrolls with chevrons at each end.'
            );
            ?>
            <?php include __DIR__ . '/_tabs.php'; ?>
        </section>

        <section id="screen-tabs" class="styleguide-section">
            <?php
            styleguideSectionHeader(
                'Screen Tabs',
                'Top-level workspaces on an edit screen, visually heavier than segmented tabs. Panels are shown in place rather than joined to the bar. Optional count badges and error dots. Set <code>data-hash="off"</code> on the nav when several groups share a page, so they do not fight over the URL hash.'
            );
            ?>
            <?php include __DIR__ . '/_screen-tabs.php'; ?>
        </section>
    </div>
</div>
