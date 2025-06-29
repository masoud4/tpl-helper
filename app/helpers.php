<?php
// app/helpers.php

error_log("DEBUG: app/helpers.php has been loaded.");

// Global variables for templating system
$GLOBALS['__tpl_layout_file'] = null;
$GLOBALS['__tpl_slots'] = [];
$GLOBALS['__tpl_current_slot'] = 'content';
$GLOBALS['__tpl_slot_stack'] = [];
$GLOBALS['__debug_timers'] = [];

/**
 * Escapes a string for HTML output to prevent XSS.
 * Usage in template: <?= e($variable) ?>
 * @param string|null $string
 * @return string
 */
function e(?string $string): string
{
    return htmlspecialchars((string) $string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Generates a URL for an asset. Can include cache busting.
 * Usage in template: <link rel="stylesheet" href="<?= css('style.css') ?>">
 * @param string $path Relative path to asset (e.g., 'css/style.css', 'js/app.js')
 * @return string Full URL to the asset.
 */
function asset(string $path): string
{
    $baseUrl = '/'; // Adjust if your app is in a subdirectory
    $fullPath = rtrim($baseUrl, '/') . '/' . ltrim($path, '/');

    // Simple cache busting: append file modification time if it exists
    // This assumes assets are in the public directory relative to $_SERVER['DOCUMENT_ROOT']
    if (isset($_SERVER['DOCUMENT_ROOT'])) {
        $filePathOnDisk = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($path, '/');
        if (file_exists($filePathOnDisk)) {
            return $fullPath . '?v=' . filemtime($filePathOnDisk);
        }
    }
    return $fullPath;
}

/**
 * Shortcut for CSS asset URL.
 * @param string $path
 * @return string
 */
function css(string $path): string
{
    return asset('css/' . ltrim($path, '/'));
}

/**
 * Shortcut for JavaScript asset URL.
 * @param string $path
 * @return string
 */
function js(string $path): string
{
    return asset('js/' . ltrim($path, '/'));
}

/**
 * Generates an application URL.
 * Usage in template: <a href="<?= url('/users/profile') ?>">Profile</a>
 * @param string $path Relative path within your application (e.g., '/users/1')
 * @return string Full URL.
 */
function url(string $path): string
{
    $baseUrl = '/'; // Adjust if your app is in a subdirectory
    return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
}

/**
 * Checks if the current URL matches the given path.
 * Useful for active navigation links.
 * @param string $path The path to check against.
 * @return bool
 */
function is_current_url(string $path): bool
{
    $currentUri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
    return rtrim($currentUri, '/') === rtrim(url($path), '/');
}

/**
 * Retrieves a segment from the current URL path.
 * Segments are 1-indexed.
 * Example: for /users/123/edit, segment(1) is 'users', segment(2) is '123'.
 * @param int $index The 1-indexed position of the segment.
 * @return string|null The segment or null if it doesn't exist.
 */
function segment(int $index): ?string
{
    $currentUri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
    $segments = array_values(array_filter(explode('/', $currentUri)));
    return $segments[$index - 1] ?? null;
}


// --- Layout Slot Helper Functions ---

/**
 * Specifies a master layout template for the current view.
 * Must be called at the very top of a content template file.
 * Usage in template: <?php tpl_layout('layout.tpl'); ?>
 * @param string $layoutFile Relative path to the layout template (e.g., 'layout.tpl').
 * @return void
 */
function tpl_layout(string $layoutFile): void
{
    $GLOBALS['__tpl_layout_file'] = $layoutFile;
}

/**
 * Starts capturing content for a named slot.
 * Usage in template: <?php tpl_startSlot('footer_scripts'); ?>
 * @param string $name The name of the slot.
 * @return void
 */
function tpl_startSlot(string $name): void
{
    $GLOBALS['__tpl_slot_stack'][] = $GLOBALS['__tpl_current_slot'];
    $GLOBALS['__tpl_current_slot'] = $name;
    ob_start();
}

/**
 * Ends capturing content for the current slot and saves it.
 * Usage in template: <?php tpl_endSlot(); ?>
 * @return void
 */
function tpl_endSlot(): void
{
    $content = ob_get_clean();
    $GLOBALS['__tpl_slots'][$GLOBALS['__tpl_current_slot']] = $content;
    $GLOBALS['__tpl_current_slot'] = array_pop($GLOBALS['__tpl_slot_stack']) ?? 'content';
}

/**
 * Retrieves the content of a named slot.
 * Usage in template: <?= tpl_slot('sidebar') ?>
 * @param string $name The name of the slot.
 * @param string $default Optional default content if the slot is empty.
 * @return string The content of the slot or the default content.
 */
function tpl_slot(string $name, string $default = ''): string
{
    return $GLOBALS['__tpl_slots'][$name] ?? $default;
}

/**
 * Renders a component template.
 * Components are small, reusable template parts that can have their own data.
 * @param string $componentName The name of the component (e.g., 'components.button' for 'templates/components/button.tpl').
 * @param array $data Optional: additional data to pass to the component.
 * @param bool $capture If true, returns the component output as a string instead of echoing.
 * @return string|void The rendered component HTML or void if not captured.
 * @throws Exception If component file not found.
 */
function tpl_component(string $componentName, array $data = [], bool $capture = false)
{
    global $GLOBALS; // Access global variables to pass them implicitly if needed by component

    $componentPath = str_replace('.', DIRECTORY_SEPARATOR, $componentName);
    $fullPath = TEMPLATE_DIRECTORY . $componentPath . '.tpl';

    if (!file_exists($fullPath)) {
        throw new Exception("Component file not found: {$fullPath}");
    }

    // Capture the current output buffer level before component render
    $currentObLevel = ob_get_level();
    ob_start();

    try {
        // Extract component-specific data (and original data for common variables like $pageTitle)
        extract($data);

        require $fullPath;
        $output = ob_get_clean();
    } catch (\Throwable $e) {
        // Ensure buffer is cleaned even on error
        while (ob_get_level() > $currentObLevel) {
            ob_end_clean();
        }
        throw new Exception("Error rendering component '{$componentName}': " . $e->getMessage(), 0, $e);
    }

    if ($capture) {
        return $output;
    } else {
        echo $output;
    }
}


/**
 * Renders a template file within a specified path and returns its output.
 * This is the main function called from your front controller (e.g., public/index.php).
 * It will handle the global layout and slots.
 * Supports dot notation for template paths (e.g., 'blog.post' maps to 'templates/blog/post.tpl').
 *
 * @param string $templateName The name of the content template (e.g., 'home' or 'pages.about').
 * @param string $templatePath The base directory where your .tpl files are located.
 * @param array $data Optional: additional data to pass to the template.
 * @return string The rendered HTML content.
 * @throws Exception If template file not found or rendering fails.
 */
function renderTemplate(string $templateName, string $templatePath, array $data = []): string
{
    // Convert dot notation to directory separator
    $templateFile = str_replace('.', DIRECTORY_SEPARATOR, $templateName) . '.tpl';
    $templateFullPath = rtrim($templatePath, '/\\') . DIRECTORY_SEPARATOR . $templateFile;

    if (!file_exists($templateFullPath)) {
        throw new Exception("Template file not found: {$templateFullPath}");
    }

    // Prepare data for the template by extracting it into the current scope.
    // This makes keys like 'pageTitle', 'username' directly available as variables.
    extract($data);

    // Start output buffering to capture the content template's output
    $currentObLevel = ob_get_level();
    ob_start();

    try {
        require $templateFullPath; // Include the content template
    } catch (\Throwable $e) {
        // Ensure buffer is cleaned even on error
        while (ob_get_level() > $currentObLevel) {
            ob_end_clean();
        }
        throw new Exception("Error rendering template '{$templateFile}': " . $e->getMessage(), 0, $e);
    }

    $content = ob_get_clean(); // Get the content of the buffer and clean it

    // If a layout was specified by the content template (via tpl_layout() function)
    if ($GLOBALS['__tpl_layout_file']) {
        $GLOBALS['__tpl_slots']['content'] = $content; // Place the captured content into the 'content' slot

        $layoutFullPath = rtrim($templatePath, '/\\') . DIRECTORY_SEPARATOR . $GLOBALS['__tpl_layout_file'];

        if (!file_exists($layoutFullPath)) {
            throw new Exception("Layout file not found: {$layoutFullPath}");
        }

        // Reset layout file and clear slots for the next render call after layout is used
        $layoutToRender = $GLOBALS['__tpl_layout_file']; // Store for this render cycle
        $GLOBALS['__tpl_layout_file'] = null; // Reset global for next request
        $GLOBALS['__tpl_current_slot'] = 'content'; // Reset default slot

        ob_start(); // Start new buffer for the layout

        // Pass essential global data to the layout (extracted here so it's fresh)
        extract($data); // Re-extract data for the layout scope

        require $layoutFullPath; // Include the layout template

        $finalOutput = ob_get_clean(); // Get the final output from the layout

        // Clear slots *after* layout is rendered using them
        $GLOBALS['__tpl_slots'] = [];

        return $finalOutput;
    }

    // If no layout was specified, just return the content directly
    return $content;
}

// --- Debugging Helpers ---

/**
 * Dumps the given variables and exits the script.
 * Similar to Laravel's dd() function.
 * @param mixed ...$args Variables to dump.
 * @return void
 */
function dd(...$args): void
{
    ini_set('html_errors', 'On'); // Ensure HTML errors are on for better output
    echo "<pre style='background-color: #333; color: #eee; padding: 20px; margin: 10px; border-radius: 8px; border: 1px solid #555; overflow-x: auto; font-family: monospace; font-size: 14px; line-height: 1.5;'>";
    echo "<strong style='color: #FFD700;'>DEBUG DUMP:</strong><br><br>";
    foreach ($args as $arg) {
        if (is_array($arg) || is_object($arg)) {
            print_r($arg);
        } else {
            var_dump($arg);
        }
        echo "\n";
    }
    echo "</pre>";
    exit(1);
}

/**
 * Starts a timer for performance measurement.
 * @param string $key A unique key for the timer.
 * @return void
 */
function time_start(string $key): void
{
    $GLOBALS['__debug_timers'][$key] = microtime(true);
}

/**
 * Ends a timer and returns/echoes the elapsed time.
 * @param string $key The unique key for the timer.
 * @param bool $echo Whether to echo the time or return it.
 * @return float|void The elapsed time in milliseconds, or void if echoed.
 */
function time_end(string $key, bool $echo = true)
{
    if (!isset($GLOBALS['__debug_timers'][$key])) {
        $message = "Timer '{$key}' was not started.";
        if ($echo) {
            echo $message;
        }
        return $echo ? void : 0.0;
    }

    $endTime = microtime(true);
    $startTime = $GLOBALS['__debug_timers'][$key];
    $elapsed = ($endTime - $startTime) * 1000; // in milliseconds

    unset($GLOBALS['__debug_timers'][$key]); // Clean up the timer

    if ($echo) {
        echo sprintf("Time for '%s': %.2f ms", $key, $elapsed);
        return;
    }
    return $elapsed;
}

// --- Data Manipulation Helpers ---

/**
 * Creates an array containing variables and their values.
 * Wrapper for PHP's compact().
 * @param string ...$varNames Variable names as strings (e.g., 'name', 'age').
 * @return array
 */
function compact_data(string ...$varNames): array
{
    return compact(...$varNames);
}

/**
 * Imports variables from an array into the current symbol table.
 * Wrapper for PHP's extract().
 * @param array $data The associative array to extract.
 * @param int $flags How to handle invalid or numeric keys, default EXTR_OVERWRITE.
 * @param string $prefix Prefix to prepend to extracted variable names.
 * @return int The number of variables successfully extracted.
 */
function extract_data(array $data, int $flags = EXTR_OVERWRITE, string $prefix = ''): int
{
    // extract() works on the current symbol table, so this helper is a simple proxy.
    return extract($data, $flags, $prefix);
}

// -----------------------------------------------------------------------------
// ADDED 100+ TEMPLATE-FRIENDLY HELPERS BELOW
// -----------------------------------------------------------------------------

// -----------------------------------------------------------------------------
// ✨ ADDITIONAL GLOBAL HELPERS FOR TEMPLATES
// Designed to help .tpl development faster
// -----------------------------------------------------------------------------

/**
 * Dump variable (no exit).
 */
function dump_var(...$vars): void
{
    echo "<pre>";
    foreach ($vars as $var) {
        var_dump($var);
    }
    echo "</pre>";
}

/**
 * Log debug to PHP error_log
 */
function debug_log(string $message): void
{
    error_log("[DEBUG] " . $message);
}

/**
 * Include a partial template file.
 */
function partial(string $file, array $data = []): void
{
    extract($data);
    include TEMPLATE_DIRECTORY . str_replace('.', DIRECTORY_SEPARATOR, $file) . '.tpl';
}

/**
 * Include and return partial output as string.
 */
function partial_capture(string $file, array $data = []): string
{
    ob_start();
    partial($file, $data);
    return ob_get_clean();
}

/**
 * Render a component with data.
 */
function component(string $name, array $data = []): void
{
    tpl_component($name, $data);
}

/**
 * Return component rendered output.
 */
function component_capture(string $name, array $data = []): string
{
    return tpl_component($name, $data, true);
}

/**
 * Simple CSRF token generator
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF hidden input field.
 */
function csrf_field(): string
{
    return '<input type="hiddenx" name="_token" value="' . (csrf_token()) . '">';
}

/**
 * Validate CSRF
 */
function validate_csrf(?string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

/**
 * Retrieve old form input.
 */
function old(string $key, $default = null)
{
    return $_SESSION['old'][$key] ?? $default;
}

/**
 * Flash a message to session.
 */
function flash(string $key, $value): void
{
    $_SESSION['flash'][$key] = $value;
}

/**
 * Get and clear flashed message.
 */
function flash_get(string $key, $default = null)
{
    if (!isset($_SESSION['flash'][$key])) {
        return $default;
    }
    $value = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $value;
}

/**
 * Simple redirect
 */
function redirect(string $url, int $status = 302): void
{
    header('Location: ' . $url, true, $status);
    exit;
}

/**
 * Current URL
 */
function current_url(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

// -----------------------------------------------------------------------------
// STRING HELPERS
// -----------------------------------------------------------------------------

function str_snake(string $string): string
{
    $string = preg_replace('/(.)(?=[A-Z])/u', '$1_', $string);
    return strtolower($string);
}

function str_kebab(string $string): string
{
    $string = preg_replace('/(.)(?=[A-Z])/u', '$1-', $string);
    return strtolower($string);
}

function str_studly(string $string): string
{
    return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string)));
}

function str_slug(string $string): string
{
    $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
    $string = preg_replace('/[^a-zA-Z0-9\/_|+ -]/', '', $string);
    $string = strtolower(trim($string, '-'));
    $string = preg_replace('/[\/_|+ -]+/', '-', $string);
    return $string;
}

function str_random(int $length = 16): string
{
    return bin2hex(random_bytes($length / 2));
}

function str_limit(string $value, int $limit = 100, string $end = '...'): string
{
    if (mb_strlen($value) <= $limit) {
        return $value;
    }
    return mb_substr($value, 0, $limit) . $end;
}

function str_ucfirst(string $string): string
{
    return ucfirst($string);
}

function str_lcfirst(string $string): string
{
    return lcfirst($string);
}

function str_title(string $string): string
{
    return ucwords($string);
}

function str_no_space(string $string): string
{
    return preg_replace('/\s+/', '', $string);
}

function str_repeat_str(string $string, int $times): string
{
    return str_repeat($string, $times);
}

function str_pad_left(string $string, int $length, string $pad = ' '): string
{
    return str_pad($string, $length, $pad, STR_PAD_LEFT);
}

function str_pad_right(string $string, int $length, string $pad = ' '): string
{
    return str_pad($string, $length, $pad, STR_PAD_RIGHT);
}

function str_replace_first(string $search, string $replace, string $subject): string
{
    $pos = strpos($subject, $search);
    if ($pos !== false) {
        return substr_replace($subject, $replace, $pos, strlen($search));
    }
    return $subject;
}

function str_replace_last(string $search, string $replace, string $subject): string
{
    $pos = strrpos($subject, $search);
    if ($pos !== false) {
        return substr_replace($subject, $replace, $pos, strlen($search));
    }
    return $subject;
}

function str_strip_quotes(string $string): string
{
    return str_replace(['"', "'"], '', $string);
}

function str_quote(string $string): string
{
    return '"' . addslashes($string) . '"';
}

function str_nl2br(string $string): string
{
    return nl2br($string);
}

function str_strip_tags(string $string): string
{
    return strip_tags($string);
}

function str_words(string $string, int $words = 10, string $end = '...'): string
{
    $array = preg_split('/\s+/', $string);
    if (count($array) <= $words) {
        return $string;
    }
    return implode(' ', array_slice($array, 0, $words)) . $end;
}

function str_reverse(string $string): string
{
    return strrev($string);
}

function str_upper(string $string): string
{
    return strtoupper($string);
}

function str_lower(string $string): string
{
    return strtolower($string);
}
