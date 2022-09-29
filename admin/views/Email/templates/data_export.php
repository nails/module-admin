<?php

if ($emailObject->data->status === 'COMPLETE') {
    echo '<p>The data export you requested has been generated; please log in to download.</p>';
    echo '<p><a href="' . siteUrl(\Nails\Admin\Admin\Controller\Utilities::url('export')) . '" class="btn">Log In</a></p>';
} else {
    echo '<p>The data export you requested failed to generate; the following error was provided: </p>';
    echo '<p>' . $emailObject->data->error . '</p>';
}
