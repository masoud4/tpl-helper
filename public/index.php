<?php
// public/index.php

// 1. Load Composer's autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// 2. Explicitly load the global helper functions.
// This ensures they are available regardless of Composer's 'files' autoload specifics.
//require_once __DIR__ . '/../app/helpers.php';

// DEBUG: Verify if the helper functions are loaded
if (!function_exists('renderTemplate') || !function_exists('dd')) {
    http_response_code(500);
    die("<h1>Configuration Error</h1><p>Core templating functions are not loaded. Please ensure 'app/helpers.php' exists and has no syntax errors, and that its path in 'public/index.php' is correct.</p>");
}

// Set the base path for your templates
// __DIR__ is the directory of the current file (public/)
// '/../templates/' goes up one level to the project root, then into 'templates'
define('TEMPLATE_DIRECTORY', __DIR__ . '/../templates/');

// --- Simulate Application Logic and Data Preparation ---
// Get the request URI, stripping any query string parameters for routing purposes
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = strtok($uri, '?'); // Remove query string (e.g., /page?id=1 becomes /page)

// Initialize data array that will be passed to the template.
// These variables will be extracted directly into the template's scope by renderTemplate().
$data = [
    'appName' => ' Global Helpers App', // Application name, used in layout
    'username' => 'GlobalUser',   // Simulating a logged-in user, used in layout
    'pageTitle' => 'Default Page', // Default title, overridden by specific routes
];

$templateToRender = ''; // This variable will store the name of the content template file
$responseStatus = 200;  // Default HTTP status code

// Simple routing logic based on the URI
switch (trim($uri, '/')) {
    case '': // Handles the root URL (e.g., http://localhost:8000/)
        $data['pageTitle'] = 'Welcome Home'; // Page-specific title
        $data['welcomeMessage'] = 'Hello from your dynamic home page using global helpers!'; // Message for home page
        $data['items'] = ['Car', 'Bicycle', 'Motorcycle', 'Train', 'Airplane']; // Sample data for list
        $templateToRender = 'home'; // No .tpl extension, will use dot notation if needed
        break;
    case 'about': // Handles /about URL
        $data['pageTitle'] = 'About Us';
        $data['aboutText'] = 'We are a dedicated team demonstrating simple PHP templating with global helper functions.';
        $templateToRender = 'about'; // No .tpl extension
        break;
    case 'contact': // Handles /contact URL
        $data['pageTitle'] = 'Contact Us';
        $data['contactInfo'] = 'Email: support@masoud4.com | Phone: +1 (555) 123-4567';
        $templateToRender = 'contact'; // No .tpl extension
        break;
    case 'debug-test': // Handles /debug-test URL for dd() example
        $data['pageTitle'] = 'Debug Test';
        $data['testVar'] = ['name' => 'John Doe', 'age' => 30, 'email' => 'john@example.com'];
        $templateToRender = 'debug_test'; // No .tpl extension
        break;
    default: // Handles any other URL, leading to a 404 Not Found error
        $responseStatus = 404; // Set HTTP status code to 404
        $data['pageTitle'] = 'Page Not Found';
        $data['errorMessage'] = 'The page you are looking for does not exist on this server.';
        $templateToRender = '404'; // No .tpl extension
        break;
}

// --- Render the page ---
try {
    // Set the HTTP response status code before any output
    http_response_code($responseStatus);

    // Call the global renderTemplate function to render the specified content template.
    // It will handle the global layout and slot system.
    // Using the template name without .tpl extension for dot-notation feature.
    echo renderTemplate($templateToRender, TEMPLATE_DIRECTORY, $data);
} catch (Exception $e) {
    // Catch any exceptions during rendering (e.g., template file not found, syntax error)
    http_response_code(500); // Set HTTP status code to 500 for internal server error
    echo "<h1>Application Error</h1>";
    echo "<p>Something went wrong: " . e($e->getMessage()) . "</p>"; // Display escaped error message using global e()
    // Log the full error for debugging purposes (e.g., to your server's error log)
    error_log("Templating Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
}