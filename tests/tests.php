<?php

require_once __DIR__ . '/testframework.php';

require_once __DIR__ . '/../site/config.php';
require_once __DIR__ . '/../site/modules/database.php';
require_once __DIR__ . '/../site/modules/page.php';

$testFramework = new TestFramework();

// test 1: check database connection
function testDbConnection() {
    global $config;
    try {
        $db = new Database($config["db"]["path"]);
        return assertExpression(true, "Database connection established successfully", "Failed to connect to database");
    } catch (Exception $e) {
        return assertExpression(false, "Database connection established successfully", "Failed to connect to database: " . $e->getMessage());
    }
}

// test 2: test count method
function testDbCount() {
    global $config;
    $db = new Database($config["db"]["path"]);
    $count = $db->Count("page");
    return assertExpression($count >= 3, "Count method returned $count (expected >= 3)", "Count method failed or returned incorrect value: $count");
}

// test 3: test create method
function testDbCreate() {
    global $config;
    $db = new Database($config["db"]["path"]);
    $initialCount = $db->Count("page");
    
    $data = [
        'title' => 'Test Page',
        'content' => 'Test Content'
    ];
    
    $id = $db->Create("page", $data);
    $newCount = $db->Count("page");
    
    return assertExpression(
        $id > 0 && $newCount == $initialCount + 1,
        "Create method created record with ID $id and count increased from $initialCount to $newCount",
        "Create method failed or count did not increase ($initialCount -> $newCount)"
    );
}

// test 4: test read method
function testDbRead() {
    global $config;
    $db = new Database($config["db"]["path"]);
    
    // Create a test record
    $data = [
        'title' => 'Read Test Page',
        'content' => 'Read Test Content'
    ];
    
    $id = $db->Create("page", $data);
    $readData = $db->Read("page", $id);
    
    return assertExpression(
        $readData && $readData['title'] == $data['title'] && $readData['content'] == $data['content'],
        "Read method correctly retrieved the record",
        "Read method failed or returned incorrect data"
    );
}

// test 5: test update method
function testDbUpdate() {
    global $config;
    $db = new Database($config["db"]["path"]);
    
    // Create a test record
    $data = [
        'title' => 'Original Title',
        'content' => 'Original Content'
    ];
    
    $id = $db->Create("page", $data);
    
    // Update the record
    $updateData = [
        'title' => 'Updated Title',
        'content' => 'Updated Content'
    ];
    
    $updateResult = $db->Update("page", $id, $updateData);
    $readData = $db->Read("page", $id);
    
    return assertExpression(
        $updateResult && $readData && $readData['title'] == $updateData['title'] && $readData['content'] == $updateData['content'],
        "Update method correctly updated the record",
        "Update method failed or did not update correctly"
    );
}

// test 6: test delete method
function testDbDelete() {
    global $config;
    $db = new Database($config["db"]["path"]);
    
    // Create a test record
    $data = [
        'title' => 'Delete Test Page',
        'content' => 'Delete Test Content'
    ];
    
    $id = $db->Create("page", $data);
    $initialCount = $db->Count("page");
    
    // Delete the record
    $deleteResult = $db->Delete("page", $id);
    $newCount = $db->Count("page");
    $readData = $db->Read("page", $id);
    
    return assertExpression(
        $deleteResult && $newCount == $initialCount - 1 && !$readData,
        "Delete method correctly deleted the record",
        "Delete method failed or did not delete correctly"
    );
}

// test 7: test execute method
function testDbExecute() {
    global $config;
    $db = new Database($config["db"]["path"]);
    
    $result = $db->Execute("UPDATE page SET title = 'Modified Title' WHERE id = 1");
    $page = $db->Read("page", 1);
    
    return assertExpression(
        $result && $page && $page['title'] == 'Modified Title',
        "Execute method properly executed SQL query",
        "Execute method failed or query did not execute correctly"
    );
}

// test 8: test fetch method
function testDbFetch() {
    global $config;
    $db = new Database($config["db"]["path"]);
    
    $results = $db->Fetch("SELECT * FROM page LIMIT 2");
    
    return assertExpression(
        is_array($results) && count($results) <= 2,
        "Fetch method returned correct array of results",
        "Fetch method failed or returned incorrect data"
    );
}

// test 9: test Page constructor
function testPageConstructor() {
    try {
        $page = new Page(__DIR__ . '/../site/templates/index.tpl');
        return assertExpression(true, "Page constructor succeeded", "Page constructor failed");
    } catch (Exception $e) {
        return assertExpression(false, "Page constructor succeeded", "Page constructor failed: " . $e->getMessage());
    }
}

// test 10: test Page render method
function testPageRender() {
    $page = new Page(__DIR__ . '/../site/templates/index.tpl');
    
    $data = [
        'title' => 'Test Title',
        'content' => 'Test Content'
    ];
    
    $rendered = $page->Render($data);
    
    return assertExpression(
        strpos($rendered, 'Test Title') !== false && strpos($rendered, 'Test Content') !== false,
        "Page render method correctly replaced template variables",
        "Page render method failed or did not replace variables correctly"
    );
}

// add tests
$testFramework->add('Database connection', 'testDbConnection');
$testFramework->add('table count', 'testDbCount');
$testFramework->add('data create', 'testDbCreate');
$testFramework->add('data read', 'testDbRead');
$testFramework->add('data update', 'testDbUpdate');
$testFramework->add('data delete', 'testDbDelete');
$testFramework->add('database execute', 'testDbExecute');
$testFramework->add('database fetch', 'testDbFetch');
$testFramework->add('page constructor', 'testPageConstructor');
$testFramework->add('page render', 'testPageRender');

// run tests
$testFramework->run();

echo $testFramework->getResult();