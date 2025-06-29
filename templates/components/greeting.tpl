<div style="border: 1px solid #ccc; padding: 10px; margin: 10px 0; background-color: #f9f9f9; border-radius: 5px;">
    <strong>Component: Greeting</strong>
    <p>
        <?= e($greetingText ?? 'Hello') ?>, <?= e($personName ?? 'Guest') ?>!
        This message is from a reusable component.
    </p>
    <?php
        // Demonstrate compact_data and extract_data within a component
        $componentVars = compact_data('personName', 'greetingText');
         var_dump($componentVars); // Uncomment to see the compact_data output in a dd()
        extract_data($componentVars); // You could re-extract here if needed, but not necessary since they're already in scope
    ?>
</div>