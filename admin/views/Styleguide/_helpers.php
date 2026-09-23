<?php

/**
 * Styleguide view helpers. Included by Styleguide/index.php.
 */

/**
 * Section title (and optional lede). The header sticks under the topbar.
 *
 * @param string      $title
 * @param string|null $lede Trusted HTML; omit when the heading is enough
 */
function styleguideSectionHeader(string $title, ?string $lede = null): void
{
    ?>
    <header class="styleguide-section__header">
        <h2 class="styleguide-section__title"><?=$title?></h2>
        <?php
        if ($lede) {
            echo '<p class="styleguide-section__lede">' . $lede . '</p>';
        }
        ?>
    </header>
    <?php
}

/**
 * @param string|null $caption Short title above the example; omit when the section heading is enough
 * @param callable    $body    Prints the example UI
 * @param array       $opts    flush: drop canvas padding so fieldsets sit as they do on a form
 */
function styleguideExample(?string $caption, callable $body, array $opts = []): void
{
    $canvas = 'styleguide-example__canvas';
    if (!empty($opts['flush'])) {
        $canvas .= ' styleguide-example__canvas--flush';
    }
    ?>
    <figure class="styleguide-example">
        <?php
        if ($caption) {
            echo '<figcaption class="styleguide-example__caption">' . $caption . '</figcaption>';
        }
        ?>
        <div class="<?=$canvas?>">
            <?php $body(); ?>
        </div>
    </figure>
    <?php
}

/**
 * Underline or segmented tab group.
 *
 * $tabs is slug => ['label' => string, 'content' => string|callable]
 *
 * @param string $group data-tabgroup value; unique on the page
 * @param array  $tabs
 * @param array  $opts  segmented, default
 */
function styleguideTabs(string $group, array $tabs, array $opts = []): void
{
    $class   = !empty($opts['segmented']) ? 'tabs tabs--segmented' : 'tabs';
    $default = $opts['default'] ?? null;
    $group   = htmlspecialchars($group);

    if ($default) {
        echo '<input type="hidden" data-tabgroup="' . $group . '" value="' . htmlspecialchars($default) . '">';
    }

    echo '<ul class="' . $class . '" data-tabgroup="' . $group . '">';
    foreach ($tabs as $slug => $tab) {
        echo '<li class="tab">';
        echo '<a href="#" data-tab="' . htmlspecialchars($slug) . '">' . $tab['label'] . '</a>';
        echo '</li>';
    }
    echo '</ul>';

    echo '<section class="' . $class . '" data-tabgroup="' . $group . '">';
    foreach ($tabs as $slug => $tab) {
        echo '<div class="tab-page ' . htmlspecialchars($slug) . '">';
        if (isset($tab['content']) && is_callable($tab['content'])) {
            $tab['content']();
        } elseif (!empty($tab['content'])) {
            echo $tab['content'];
        }
        echo '</div>';
    }
    echo '</section>';
}

/**
 * A labelled field using the real admin form helper, not styleguide-only CSS.
 */
function styleguideField(string $label, string $value, array $opts = []): void
{
    static $i = 0;
    $i++;

    $aField = [
        'key'     => $opts['key'] ?? ('sg-field-' . $i),
        'label'   => $label,
        'default' => $value,
    ];

    if (!empty($opts['error'])) {
        $aField['error'] = $opts['error'] === true ? 'This field is required.' : $opts['error'];
    }

    if (!empty($opts['hint'])) {
        $aField['sub_label'] = $opts['hint'];
    }

    echo form_field($aField);
}

function styleguideSaveBar(): void
{
    ?>
    <div class="admin-floating-controls">
        <button type="button" class="btn btn-primary">Save changes</button>
    </div>
    <?php
}
