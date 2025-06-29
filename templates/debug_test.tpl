<?php tpl_layout('layout.tpl'); ?>
<?php tpl_startSlot('content'); ?>
    <h2>Debug Test Page</h2>
    <p>This page demonstrates the `dd()` helper function.</p>
    <p>Click the button below to trigger a debug dump of the `$testVar`.</p>
    <form method="post">
        <button type="submit" name="dump_data">Dump Data (dd)</button>
    </form>

    <?php
        // This is where you'd typically call dd() based on some condition
        // For demonstration, we'll trigger it if the button is pressed.
        if (isset($_POST['dump_data'])) {
            dd("This is a debug message from debug_test.tpl", $testVar, ['extra_info' => 'This object will also be dumped']);
        }
    ?>

    <p>This text will only be visible if `dd()` is NOT triggered.</p>
<?php tpl_endSlot(); ?>

<?php tpl_startSlot('sidebar'); ?>
    <h3>Debug Info</h3>
    <p>This sidebar is also part of the debug test.</p>
<?php tpl_endSlot(); ?>