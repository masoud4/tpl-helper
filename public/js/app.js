// public/js/app.js
console.log("Global app.js loaded!");

document.addEventListener('DOMContentLoaded', () => {
    const navLinks = document.querySelectorAll('nav a');
    navLinks.forEach(link => {
        link.addEventListener('click', (event) => {
            console.log(`Navigating to: ${event.target.href}`);
        });
    });

    const appNameElement = document.querySelector('header h1');
    if (appNameElement) {
        console.log(`App name from header: "${appNameElement.textContent}"`);
    }
});