    <?php   dd(csrf_field()); ?>      
<?php tpl_layout('layout.tpl'); ?> <!-- Renamed function -->
<?php tpl_startSlot('content'); ?> <!-- Renamed function -->
    <h2>Contact Us</h2>
  
         <?php if (isset($contactInfo)): ?>
        <p>You can reach us here:</p>
        <p><strong><?= e($contactInfo) ?></strong></p>
    <?php endif; ?>
    <p>We are always happy to hear from you. Please use the provided contact details for any inquiries or support.</p>
    <form action="/submit-contact" method="post" style="max-width: 500px; margin-top: 20px; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #fdfdfd;">
        <label for="name" style="display: block; margin-bottom: 5px; font-weight: bold;">Your Name:</label>
        <input type="text" id="name" name="name" style="width: calc(100% - 10px); padding: 8px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;" required><br>

        <label for="email" style="display: block; margin-bottom: 5px; font-weight: bold;">Your Email:</label>
        <input type="email" id="email" name="email" style="width: calc(100% - 10px); padding: 8px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;" required><br>

        <label for="message" style="display: block; margin-bottom: 5px; font-weight: bold;">Your Message:</label>
        <textarea id="message" name="message" rows="5" style="width: calc(100% - 10px); padding: 8px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 4px;" required></textarea><br>

        <button type="submit" style="background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 1em;">Send Message</button>
    </form>
<?php tpl_endSlot(); ?> <!-- Renamed function -->

<?php tpl_startSlot('footer_scripts'); ?> <!-- Renamed function -->
    <script>
        console.log("Contact page specific script loaded!");
        const contactForm = document.querySelector('form');
        if (contactForm) {
            contactForm.addEventListener('submit', (event) => {
                const name = document.getElementById('name').value.trim();
                const email = document.getElementById('email').value.trim();
                const message = document.getElementById('message').value.trim();

                if (name === '' || email === '' || message === '') {
                    alert('Please fill in all fields.');
                    event.preventDefault();
                } else if (!email.includes('@')) {
                    alert('Please enter a valid email address.');
                    event.preventDefault();
                } else {
                    console.log('Form submitted (client-side validation passed).');
                }
            });
        }
    </script>
<?php tpl_endSlot(); ?> <!-- Renamed function -->
