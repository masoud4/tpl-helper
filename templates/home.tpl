<?php tpl_layout('layout.tpl'); ?>
<?php tpl_startSlot('content'); ?>
    <h2>Welcome to the Home Page!</h2>

    <?php if (isset($welcomeMessage)): ?>
        <p><?= e($welcomeMessage) ?></p>
       
    <?php endif; ?>

    <h3>Featured Items:</h3>
    <?php if (!empty($items)): ?>
        <ul>
            <?php foreach ($items as $item): ?>
                <li><?= e($item) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No featured items available.</p>
    <?php endif; ?>

    <hr>
    <h3>Component Example:</h3>
    <?php
        // Measure time for rendering a component
        time_start('greeting_component');
        // Render a component, passing specific data to it
        tpl_component('components.greeting', ['personName' => 'Alice', 'greetingText' => 'Hello there']);
        time_end('greeting_component');
    ?>

    <hr>
    <h3>Another Component Example (Captured):</h3>
    <?php
        // Render another component and capture its output to a variable
        $capturedGreeting = tpl_component('components.greeting', ['personName' => 'Bob', 'greetingText' => 'Greetings!'], true);
        echo "<p>Captured component output: " . e($capturedGreeting) . "</p>";
    ?>

<?php tpl_endSlot(); ?>

<?php tpl_startSlot('sidebar'); ?>
    <h3>Quick Links</h3>
    <ul>
        <li><a href="<?= url('/products') ?>">Products</a></li>
        <li><a href="<?= url('/services') ?>">Services</a></li>
        <li><a href="https://github.com/Masoud4" target="_blank">My GitHub</a></li>
    </ul>
    <p>Current URL path segment 1: **<?= e(segment(1)) ?>**</p>
<?php tpl_endSlot(); ?>

<?php tpl_startSlot('footer_scripts'); ?>
    <script>
        console.log("Home page specific script loaded!");
        const featuredItemsList = document.querySelector('h3 + ul');
        if (featuredItemsList) {
            featuredItemsList.addEventListener('click', (event) => {
                if (event.target.tagName === 'LI') {
                    alert('You clicked on: ' + event.target.textContent);
                }
            });
        }
    </script>
<?php tpl_endSlot(); ?>