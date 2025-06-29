<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'My App') ?></title>
    <link rel="stylesheet" href="<?= css('style.css') ?>">
</head>
<body>
    <header>
        <h1><?= e($appName ?? 'Unnamed App') ?></h1>
        <nav>
            <ul>
                <li><a href="<?= url('/') ?>">Home</a></li>
                <li><a href="<?= url('/about') ?>">About</a></li>
                <li><a href="<?= url('/contact') ?>">Contact</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="content-area">
            <div class="slot-container">
                <div class="slot-title">Main Content:</div>
                <?= tpl_slot('content') ?> <!-- Renamed function -->
            </div>
        </div>
        <div class="sidebar-area">
            <div class="slot-container">
                <div class="slot-title">Sidebar:</div>
                <?= tpl_slot('sidebar', '<p>Default sidebar content.</p>') ?> <!-- Renamed function -->
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> My App. Current user: <?= e($username ?? 'Guest') ?></p>
        <?= tpl_slot('footer_scripts') ?> <!-- Renamed function -->
    </footer>
    <script src="<?= js('app.js') ?>"></script>
</body>
</html>