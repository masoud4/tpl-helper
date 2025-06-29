<?php tpl_layout('layout.tpl'); ?> <!-- Renamed function -->
<?php tpl_startSlot('content'); ?> <!-- Renamed function -->
    <h2>About Us</h2>
    <?php if (isset($aboutText)): ?>
        <p><?= e($aboutText) ?></p>
    <?php endif; ?>
    <p>This application demonstrates a simple PHP templating system with custom helpers and layout support. It's built to be easily understandable and extendable.</p>
    <p>We believe in clear separation of concerns, making development smoother for both backend logic and front-end design.</p>
<?php tpl_endSlot(); ?> <!-- Renamed function -->

<?php tpl_startSlot('sidebar'); ?> <!-- Renamed function -->
    <h3>Our Mission</h3>
    <p>Our mission is to provide robust and easy-to-use tools for PHP developers, fostering clean code and efficient development workflows.</p>
<?php tpl_endSlot(); ?> <!-- Renamed function -->