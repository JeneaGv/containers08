<?php

require_once __DIR__ . '/modules/database.php';
require_once __DIR__ . '/modules/page.php';

require_once __DIR__ . '/config.php';

$db = new Database($config["db"]["path"]);

$page = new Page(__DIR__ . '/templates/index.tpl');

// Get page ID from request, default to 1 if not specified
$pageId = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Validate page ID
if ($pageId <= 0) {
    $pageId = 1;
}

// Read page data from database
$data = $db->Read("page", $pageId);

// If page doesn't exist, show default content
if (!$data) {
    $data = [
        'title' => 'Page Not Found',
        'content' => 'The requested page does not exist.'
    ];
}

// Render and display the page
echo $page->Render($data);