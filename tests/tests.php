<?php

require_once __DIR__ . '/testframework.php';

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../modules/database.php';
require_once __DIR__ . '/../modules/page.php';

$tests = new TestFramework();

// test 1: check database connection
function testDbConnection() {
    global $config;
    $db = new Database($config['db']);
    
    // Verificăm conexiunea executând o interogare simplă
    try {
        $result = $db->query("SELECT 1 as test");
        return $result !== false && is_array($result);
    } catch (Exception $e) {
        return false;
    }
}

// test 2: test count method
function testDbCount() {
    global $config;
    $db = new Database($config['db']);
    
    // Verificăm că putem număra rândurile dintr-un tabel
    $count = $db->count('pages');
    return is_numeric($count) && $count >= 0;
}

// test 3: test create method
function testDbCreate() {
    global $config;
    $db = new Database($config['db']);
    
    // Generăm un titlu unic pentru test
    $testTitle = 'Test Page ' . uniqid();
    
    // Creăm o pagină de test
    $data = [
        'title' => $testTitle,
        'content' => 'Test content',
        'slug' => 'test-page-' . uniqid(),
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $id = $db->create('pages', $data);
    
    // Verificăm că a fost returnat un ID valid
    if (!is_numeric($id) || $id <= 0) {
        return false;
    }
    
    // Curățăm după test
    $db->delete('pages', $id);
    
    return true;
}

// test 4: test read method
function testDbRead() {
    global $config;
    $db = new Database($config['db']);
    
    // Generăm un titlu unic pentru test
    $testTitle = 'Test Page ' . uniqid();
    
    // Creăm o pagină de test
    $data = [
        'title' => $testTitle,
        'content' => 'Test content',
        'slug' => 'test-page-' . uniqid(),
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $id = $db->create('pages', $data);
    
    // Citim pagina creată
    $page = $db->read('pages', $id);
    
    // Curățăm după test
    $db->delete('pages', $id);
    
    // Verificăm că datele citite corespund cu cele create
    return is_array($page) && $page['title'] === $testTitle;
}

// test 5: test update method
function testDbUpdate() {
    global $config;
    $db = new Database($config['db']);
    
    // Generăm un titlu unic pentru test
    $testTitle = 'Test Page ' . uniqid();
    
    // Creăm o pagină de test
    $data = [
        'title' => $testTitle,
        'content' => 'Test content',
        'slug' => 'test-page-' . uniqid(),
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $id = $db->create('pages', $data);
    
    // Actualizăm pagina
    $newTitle = 'Updated Page ' . uniqid();
    $updateData = [
        'title' => $newTitle
    ];
    
    $updated = $db->update('pages', $id, $updateData);
    
    // Verificăm că actualizarea a reușit
    if (!$updated) {
        $db->delete('pages', $id);
        return false;
    }
    
    // Citim pagina actualizată
    $page = $db->read('pages', $id);
    
    // Curățăm după test
    $db->delete('pages', $id);
    
    // Verificăm că titlul a fost actualizat
    return is_array($page) && $page['title'] === $newTitle;
}

// test 6: test delete method
function testDbDelete() {
    global $config;
    $db = new Database($config['db']);
    
    // Generăm un titlu unic pentru test
    $testTitle = 'Test Page ' . uniqid();
    
    // Creăm o pagină de test
    $data = [
        'title' => $testTitle,
        'content' => 'Test content',
        'slug' => 'test-page-' . uniqid(),
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $id = $db->create('pages', $data);
    
    // Ștergem pagina
    $deleted = $db->delete('pages', $id);
    
    // Verificăm că ștergerea a reușit
    if (!$deleted) {
        return false;
    }
    
    // Încercăm să citim pagina ștearsă
    $page = $db->read('pages', $id);
    
    // Verificăm că pagina nu mai există
    return $page === null || empty($page);
}

// test 7: test query method
function testDbQuery() {
    global $config;
    $db = new Database($config['db']);
    
    // Executăm un query simplu
    $result = $db->query("SELECT 1 as test");
    
    // Verificăm că rezultatul este un array și conține datele așteptate
    return is_array($result) && count($result) > 0 && isset($result[0]['test']) && $result[0]['test'] == 1;
}

// test 8: test connection/PDO getter method
function testDbGetConnection() {
    global $config;
    $db = new Database($config['db']);
    
    // Verificăm că putem obține conexiunea PDO
    $connection = $db->getConnection();
    
    // Verificăm că este o instanță PDO
    return $connection instanceof PDO;
}

// test 9: test findAll method
function testDbFindAll() {
    global $config;
    $db = new Database($config['db']);
    
    // Generăm un titlu unic pentru test
    $testMarker = uniqid('findall_test_');
    
    // Creăm câteva pagini de test
    $data1 = [
        'title' => 'Test Page 1 ' . $testMarker,
        'content' => 'Test content 1',
        'slug' => 'test-page-1-' . $testMarker,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $data2 = [
        'title' => 'Test Page 2 ' . $testMarker,
        'content' => 'Test content 2',
        'slug' => 'test-page-2-' . $testMarker,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $id1 = $db->create('pages', $data1);
    $id2 = $db->create('pages', $data2);
    
    // Căutăm paginile create
    $where = "title LIKE '%{$testMarker}%'";
    $pages = $db->findAll('pages', $where);
    
    // Curățăm după test
    $db->delete('pages', $id1);
    $db->delete('pages', $id2);
    
    // Verificăm că am găsit cel puțin 2 pagini
    return is_array($pages) && count($pages) >= 2;
}

// test 10: test findOne method
function testDbFindOne() {
    global $config;
    $db = new Database($config['db']);
    
    // Generăm un slug unic pentru test
    $testSlug = 'test-page-' . uniqid();
    
    // Creăm o pagină de test
    $data = [
        'title' => 'Test Page',
        'content' => 'Test content',
        'slug' => $testSlug,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $id = $db->create('pages', $data);
    
    // Căutăm pagina după slug
    $where = "slug = '{$testSlug}'";
    $page = $db->findOne('pages', $where);
    
    // Curățăm după test
    $db->delete('pages', $id);
    
    // Verificăm că am găsit pagina corectă
    return is_array($page) && $page['slug'] === $testSlug;
}

// Test Page class - test 1: constructor and setters/getters
function testPageConstructor() {
    $page = new Page();
    
    // Testăm setters și getters
    $page->setId(1);
    $page->setTitle('Test Title');
    $page->setContent('Test Content');
    $page->setSlug('test-title');
    $page->setCreatedAt('2023-01-01 12:00:00');
    
    return $page->getId() === 1 &&
           $page->getTitle() === 'Test Title' &&
           $page->getContent() === 'Test Content' &&
           $page->getSlug() === 'test-title' &&
           $page->getCreatedAt() === '2023-01-01 12:00:00';
}

// Test Page class - test 2: fromArray method
function testPageFromArray() {
    $data = [
        'id' => 1,
        'title' => 'Test Title',
        'content' => 'Test Content',
        'slug' => 'test-title',
        'created_at' => '2023-01-01 12:00:00'
    ];
    
    $page = Page::fromArray($data);
    
    return $page->getId() === 1 &&
           $page->getTitle() === 'Test Title' &&
           $page->getContent() === 'Test Content' &&
           $page->getSlug() === 'test-title' &&
           $page->getCreatedAt() === '2023-01-01 12:00:00';
}

// Test Page class - test 3: toArray method
function testPageToArray() {
    $page = new Page();
    $page->setId(1);
    $page->setTitle('Test Title');
    $page->setContent('Test Content');
    $page->setSlug('test-title');
    $page->setCreatedAt('2023-01-01 12:00:00');
    
    $data = $page->toArray();
    
    return $data['id'] === 1 &&
           $data['title'] === 'Test Title' &&
           $data['content'] === 'Test Content' &&
           $data['slug'] === 'test-title' &&
           $data['created_at'] === '2023-01-01 12:00:00';
}

// Test Page class - test 4: save method (create new page)
function testPageSaveCreate() {
    global $config;
    
    $page = new Page();
    $page->setTitle('Test Save Create ' . uniqid());
    $page->setContent('Test Content');
    $page->setSlug('test-save-create-' . uniqid());
    
    // Salvăm pagina (create)
    $result = $page->save();
    
    // Verificăm că a fost salvată cu succes
    if (!$result || !$page->getId()) {
        return false;
    }
    
    // Verificăm că avem data creării
    if (empty($page->getCreatedAt())) {
        $db = new Database($config['db']);
        $db->delete('pages', $page->getId());
        return false;
    }
    
    // Curățăm după test
    $db = new Database($config['db']);
    $db->delete('pages', $page->getId());
    
    return true;
}

// Test Page class - test 5: save method (update existing page)
function testPageSaveUpdate() {
    global $config;
    
    // Creăm o pagină
    $page = new Page();
    $page->setTitle('Test Save Update ' . uniqid());
    $page->setContent('Test Content');
    $page->setSlug('test-save-update-' . uniqid());
    $page->save();
    
    $id = $page->getId();
    
    // Actualizăm pagina
    $page->setTitle('Updated Title ' . uniqid());
    $result = $page->save();
    
    // Verificăm că a fost actualizată cu succes
    if (!$result) {
        $db = new Database($config['db']);
        $db->delete('pages', $id);
        return false;
    }
    
    // Verificăm că ID-ul nu s-a schimbat
    if ($page->getId() !== $id) {
        $db = new Database($config['db']);
        $db->delete('pages', $id);
        return false;
    }
    
    // Verificăm că titlul a fost actualizat în baza de date
    $db = new Database($config['db']);
    $savedPage = $db->read('pages', $id);
    
    // Curățăm după test
    $db->delete('pages', $id);
    
    return $savedPage['title'] === $page->getTitle();
}

// Test Page class - test 6: delete method
function testPageDelete() {
    global $config;
    
    // Creăm o pagină
    $page = new Page();
    $page->setTitle('Test Delete ' . uniqid());
    $page->setContent('Test Content');
    $page->setSlug('test-delete-' . uniqid());
    $page->save();
    
    $id = $page->getId();
    
    // Ștergem pagina
    $result = $page->delete();
    
    // Verificăm că a fost ștearsă cu succes
    if (!$result) {
        $db = new Database($config['db']);
        $db->delete('pages', $id);
        return false;
    }
    
    // Verificăm că nu mai există în baza de date
    $db = new Database($config['db']);
    $savedPage = $db->read('pages', $id);
    
    return $savedPage === null || empty($savedPage);
}

// Test Page class - test 7: findById method
function testPageFindById() {
    global $config;
    
    // Creăm o pagină
    $db = new Database($config['db']);
    $testTitle = 'Test FindById ' . uniqid();
    $data = [
        'title' => $testTitle,
        'content' => 'Test Content',
        'slug' => 'test-findbyid-' . uniqid(),
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $id = $db->create('pages', $data);
    
    // Încercăm să găsim pagina după ID
    $page = Page::findById($id);
    
    // Curățăm după test
    $db->delete('pages', $id);
    
    // Verificăm că am găsit pagina corectă
    return $page instanceof Page && $page->getId() == $id && $page->getTitle() === $testTitle;
}

// Test Page class - test 8: findBySlug method
function testPageFindBySlug() {
    global $config;
    
    // Creăm o pagină
    $db = new Database($config['db']);
    $testTitle = 'Test FindBySlug ' . uniqid();
    $testSlug = 'test-findbyslug-' . uniqid();
    $data = [
        'title' => $testTitle,
        'content' => 'Test Content',
        'slug' => $testSlug,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $id = $db->create('pages', $data);
    
    // Încercăm să găsim pagina după slug
    $page = Page::findBySlug($testSlug);
    
    // Curățăm după test
    $db->delete('pages', $id);
    
    // Verificăm că am găsit pagina corectă
    return $page instanceof Page && $page->getId() == $id && $page->getSlug() === $testSlug;
}

// Test Page class - test 9: findAll method
function testPageFindAll() {
    global $config;
    
    // Creăm câteva pagini
    $db = new Database($config['db']);
    $testMarker = uniqid('findall_page_test_');
    
    $data1 = [
        'title' => 'Test Page 1 ' . $testMarker,
        'content' => 'Test content 1',
        'slug' => 'test-page-1-' . $testMarker,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $data2 = [
        'title' => 'Test Page 2 ' . $testMarker,
        'content' => 'Test content 2',
        'slug' => 'test-page-2-' . $testMarker,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $id1 = $db->create('pages', $data1);
    $id2 = $db->create('pages', $data2);
    
    // Căutăm paginile create
    $pages = Page::findAll("title LIKE '%{$testMarker}%'");
    
    // Curățăm după test
    $db->delete('pages', $id1);
    $db->delete('pages', $id2);
    
    // Verificăm că am găsit cel puțin 2 pagini și că sunt instanțe Page
    $allArePageInstances = true;
    foreach ($pages as $page) {
        if (!($page instanceof Page)) {
            $allArePageInstances = false;
            break;
        }
    }
    
    return is_array($pages) && count($pages) >= 2 && $allArePageInstances;
}

// Test Page class - test 10: generateSlug method
function testPageGenerateSlug() {
    $title = 'This is a Test Title with Spaces and Special Characters! @#$%^&*()';
    $slug = Page::generateSlug($title);
    
    // Verificăm că slug-ul este generat corect
    return $slug === 'this-is-a-test-title-with-spaces-and-special-characters' || 
           $slug === 'this-is-a-test-title-with-spaces-and-special-characters-';
}

// add tests for Database class
$tests->add('Database connection', 'testDbConnection');
$tests->add('Database count method', 'testDbCount');
$tests->add('Database create method', 'testDbCreate');
$tests->add('Database read method', 'testDbRead');
$tests->add('Database update method', 'testDbUpdate');
$tests->add('Database delete method', 'testDbDelete');
$tests->add('Database query method', 'testDbQuery');
$tests->add('Database getConnection method', 'testDbGetConnection');
$tests->add('Database findAll method', 'testDbFindAll');
$tests->add('Database findOne method', 'testDbFindOne');

// add tests for Page class
$tests->add('Page constructor and getters/setters', 'testPageConstructor');
$tests->add('Page fromArray method', 'testPageFromArray');
$tests->add('Page toArray method', 'testPageToArray');
$tests->add('Page save (create) method', 'testPageSaveCreate');
$tests->add('Page save (update) method', 'testPageSaveUpdate');
$tests->add('Page delete method', 'testPageDelete');
$tests->add('Page findById method', 'testPageFindById');
$tests->add('Page findBySlug method', 'testPageFindBySlug');
$tests->add('Page findAll method', 'testPageFindAll');
$tests->add('Page generateSlug method', 'testPageGenerateSlug');

// run tests
$tests->run();

echo $tests->getResult();