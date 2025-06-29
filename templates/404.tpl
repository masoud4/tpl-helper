<?php tpl_layout('layout.tpl'); ?> <!-- Renamed function -->
<?php tpl_startSlot('content'); ?> <!-- Renamed function -->
    <h2 style="color: #dc3545;">404 - Page Not Found</h2>
    <?php if (isset($errorMessage)): ?>
        <p style="font-size: 1.1em;"><?= e($errorMessage) ?></p>
    <?php endif; ?>
    <p>The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
    <p>Please check the URL for any typos, or return to the <a href="<?= url('/') ?>">home page</a>.</p>
<?php tpl_endSlot(); ?> <!-- Renamed function -->

<?php tpl_startSlot('sidebar'); ?> <!-- Renamed function -->
    <h3>What to do?</h3>
    <p>If you're unsure where to go, here are some options:</p>
    <ul>
        <li><a href="<?= url('/') ?>">Go to Home</a></li>
        <li><a href="<?= url('/about') ?>">Learn About Us</a></li>
        <li><a href="<?= url('/contact') ?>">Contact Support</a></li>
    </ul>
<?php tpl_endSlot(); ?> <!-- Renamed function -->