<div class="group-admin group-admin-styleguide">
    <p class="alert alert-info">
        @todo: Complete a style guide for admin showing all the components and how the current admin stylesheet/theme
        renders them.
    </p>
    <hr />

    <!-- Typography -->
    <section class="typography">
        <div class="title">
            Typography
        </div>
        <div class="description">
            Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
            tempor incididunt ut labore et dolore magna aliqua.
        </div>
        <div class="body">
            <h1>This is a &lt;h1&gt; heading</h1>
            <h2>This is a &lt;h2&gt; heading</h2>
            <h3>This is a &lt;h3&gt; heading</h3>
            <h4>This is a &lt;h4&gt; heading</h4>
            <h5>This is a &lt;h5&gt; heading</h5>
            <h6>This is a &lt;h6&gt; heading</h6>
            <p>
                This is some body text. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
                quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
                consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
                cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
                proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
            </p>
            <p>
                This is some body text. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
                quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
                consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
                cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
                proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
            </p>
            <p>
                <small>This is some small text</small>
            </p>
            <p>
                <strong>This is some strong text</strong>
            </p>
            <p>
                <em>This is some italic text</em>
            </p>
        </div>
    </section>
    <!-- /Typography -->

    <!-- Colours -->
    <section class="alerts">
        <div class="title">
            Colours
        </div>
        <div class="body">
        </div>
    </section>
    <!-- /Colours -->

    <!-- Buttons -->
    <section class="alerts">
        <div class="title">
            Buttons
        </div>
        <div class="body">
            <?php

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
                echo '<p>';
                echo 'Size: <code>' . ($sSize ?: 'undefined') . '</code>';
                echo '</p>';
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

            ?>
        </div>
    </section>
    <!-- /Buttons -->

    <!-- Badges -->
    <section class="alerts">
        <div class="title">
            Badges
        </div>
        <div class="body">
            <?php

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

            ?>
        </div>
    </section>
    <!-- /Buttons -->

    <!-- Alerts -->
    <section class="alerts">
        <div class="title">
            Alerts
        </div>
        <div class="body">
            <?php

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

            ?>
        </div>
    </section>
    <!-- /Alerts -->

    <!-- Tables -->
    <section class="alerts">
        <div class="title">
            Tables
        </div>
        <div class="body">
            <table>
                <thead>
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
        </div>
    </section>
    <!-- /Tables -->

    <!-- Fieldsets -->
    <section class="fieldsets">
        <div class="title">
            Fieldsets
        </div>
        <div class="body">
            <p>
                An example of a normal, top level fieldset.
            </p>
            <fieldset>
                <legend>I Am Legend</legend>
                <p>
                    Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                    tempor incididunt ut labore et dolore magna aliqua.
                </p>
                <table>
                    <thead>
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
                <p>
                    Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                    tempor incididunt ut labore et dolore magna aliqua.
                </p>
            </fieldset>
            <p>
                An example of a nested fieldset.
            </p>
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
                    <table>
                        <thead>
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
                    <p>
                        Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                        tempor incididunt ut labore et dolore magna aliqua.
                    </p>
                </fieldset>
            </fieldset>
        </div>
    </section>
    <!-- /Fieldsets -->

    <!-- Tabs -->
    <section>
        <div class="title">
            Tabs
        </div>
        <div class="body">
            <p>
                The following tab group has no implicit grouping and is independent:
            </p>
            <ul class="tabs">
                <li class="tab">
                    <a href="#" data-tab="tab-one">
                        Tab 1
                    </a>
                </li>
                <li class="tab">
                    <a href="#" data-tab="tab-two">
                        Tab 2
                    </a>
                </li>
                <li class="tab">
                    <a href="#" data-tab="tab-three">
                        Tab 3
                    </a>
                </li>
            </ul>
            <section class="tabs">
                <div class="tab-page tab-one">
                    <p>
                        Tab panel one.
                    </p>
                </div>
                <div class="tab-page tab-two">
                    <p>
                        Tab panel two.
                    </p>
                </div>
                <div class="tab-page tab-three">
                    <p>
                        Tab panel three.
                    </p>
                </div>
            </section>

            <p>
                The following tab group is nested and act independently:
            </p>
            <ul class="tabs">
                <li class="tab">
                    <a href="#" data-tab="tab-one">
                        Tab 1
                    </a>
                </li>
                <li class="tab">
                    <a href="#" data-tab="tab-two">
                        Tab 2
                    </a>
                </li>
                <li class="tab">
                    <a href="#" data-tab="tab-three">
                        Tab 3
                    </a>
                </li>
            </ul>
            <section class="tabs">
                <div class="tab-page tab-one">
                    <ul class="tabs">
                        <li class="tab">
                            <a href="#" data-tab="tab-one">
                                Tab 1
                            </a>
                        </li>
                        <li class="tab">
                            <a href="#" data-tab="tab-two">
                                Tab 2
                            </a>
                        </li>
                        <li class="tab">
                            <a href="#" data-tab="tab-three">
                                Tab 3
                            </a>
                        </li>
                    </ul>
                    <section class="tabs">
                        <div class="tab-page tab-one">
                            <p>
                                Tab panel one.
                            </p>
                        </div>
                        <div class="tab-page tab-two">
                            <p>
                                Tab panel two.
                            </p>
                        </div>
                        <div class="tab-page tab-three">
                            <p>
                                Tab panel three.
                            </p>
                        </div>
                    </section>
                </div>
                <div class="tab-page tab-two">
                    <p>
                        Tab panel two.
                    </p>
                </div>
                <div class="tab-page tab-three">
                    <p>
                        Tab panel three.
                    </p>
                </div>
            </section>

            <p>
                The following tab groups mimic each other:
            </p>
            <ul class="tabs" data-tabgroup="tabs-mimic">
                <li class="tab">
                    <a href="#" data-tab="tab-one">
                        Tab 1
                    </a>
                </li>
                <li class="tab">
                    <a href="#" data-tab="tab-two">
                        Tab 2
                    </a>
                </li>
                <li class="tab">
                    <a href="#" data-tab="tab-three">
                        Tab 3
                    </a>
                </li>
            </ul>
            <section class="tabs" data-tabgroup="tabs-mimic">
                <div class="tab-page tab-one">
                    <p>
                        Tab panel one.
                    </p>
                </div>
                <div class="tab-page tab-two">
                    <p>
                        Tab panel two.
                    </p>
                </div>
                <div class="tab-page tab-three">
                    <p>
                        Tab panel three.
                    </p>
                </div>
            </section>

            <ul class="tabs" data-tabgroup="tabs-mimic">
                <li class="tab">
                    <a href="#" data-tab="tab-one">
                        Tab 1
                    </a>
                </li>
                <li class="tab">
                    <a href="#" data-tab="tab-two">
                        Tab 2
                    </a>
                </li>
                <li class="tab">
                    <a href="#" data-tab="tab-three">
                        Tab 3
                    </a>
                </li>
            </ul>
            <section class="tabs" data-tabgroup="tabs-mimic">
                <div class="tab-page tab-one">
                    <p>
                        Tab panel one.
                    </p>
                </div>
                <div class="tab-page tab-two">
                    <p>
                        Tab panel two.
                    </p>
                </div>
                <div class="tab-page tab-three">
                    <p>
                        Tab panel three.
                    </p>
                </div>
            </section>

            <p>
                The following tab group defaults to the second tab:
            </p>
            <input type="hidden" data-tabgroup="tab-group-one" value="tab-two" />
            <ul class="tabs" data-tabgroup="tab-group-one" data-active-tab-input="#tab-group-one">
                <li class="tab">
                    <a href="#" data-tab="tab-one">
                        Tab 1
                    </a>
                </li>
                <li class="tab">
                    <a href="#" data-tab="tab-two">
                        Tab 2
                    </a>
                </li>
                <li class="tab">
                    <a href="#" data-tab="tab-three">
                        Tab 3
                    </a>
                </li>
            </ul>
            <section class="tabs" data-tabgroup="tab-group-one">
                <div class="tab-page tab-one">
                    <p>
                        Tab panel one.
                    </p>
                </div>
                <div class="tab-page tab-two">
                    <p>
                        Tab panel two.
                    </p>
                </div>
                <div class="tab-page tab-three">
                    <p>
                        Tab panel three.
                    </p>
                </div>
            </section>

            <p>
                The following tab group defaults to the first panel containing an error:
            </p>
            <ul class="tabs" data-tabgroup="tab-group-two" data-active-tab-input="#tab-group-two">
                <li class="tab">
                    <a href="#" data-tab="tab-one">
                        Tab 1
                    </a>
                </li>
                <li class="tab">
                    <a href="#" data-tab="tab-two">
                        Tab 2
                    </a>
                </li>
                <li class="tab">
                    <a href="#" data-tab="tab-three">
                        Tab 3
                    </a>
                </li>
            </ul>
            <section class="tabs" data-tabgroup="tab-group-two">
                <div class="tab-page tab-one">
                    <p>
                        Tab panel one.
                    </p>
                </div>
                <div class="tab-page tab-two">
                    <p class="alert alert-danger">
                        Tab panel two.
                    </p>
                </div>
                <div class="tab-page tab-three">
                    <p class="alert alert-danger">
                        Tab panel three.
                    </p>
                </div>
            </section>
        </div>
    </section>
    <!-- /Tabs -->
</div>
