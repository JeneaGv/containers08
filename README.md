# Integrare continuă cu Github Actions

# Scopul lucrării

În cadrul acestei lucrări studenții vor învăța să configureze integrarea continuă cu ajutorul Github Actions.

# Sarcina

Crearea unei aplicații Web, scrierea testelor pentru aceasta și configurarea integrării continue cu ajutorul Github Actions pe baza containerelor.

# Execuție

Cream un repozitoriu containers08 și copiem pe computerul dvs.

În directorul containers08 cream directorul ./site. În directorul ./site va fi plasată aplicația Web pe baza PHP.

# Crearea aplicației Web 

Cream în directorul ./site aplicația Web pe baza PHP cu următoarea structură:

![image](https://github.com/user-attachments/assets/a9706804-240b-4c8c-b2df-66cf2356b52c)

Fișierul modules/database.php conține clasa Database pentru lucru cu baza de date. Pentru lucru cu baza de date folosiți SQLite. Clasa trebuie să conțină metode:

Fișierul modules/page.php conține clasa Page pentru lucru cu paginile. Clasa trebuie să conțină metode:

Fișierul templates/index.tpl conține șablonul paginii.

Fișierul styles/style.css conține stilurile pentru pagina.

Fișierul index.php conține codul pentru afișarea paginii. Un exemplu de cod pentru fișierul index.php:

Fișierul config.php conține setările pentru conectarea la baza de date.

# Pregătirea fișierului SQL pentru baza de date

Cream în directorul ./site directorul ./sql. În directorul creat cream fișierul schema.sql cu următorul conținut:

CREATE TABLE page (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT,
    content TEXT
);

INSERT INTO page (title, content) VALUES ('Page 1', 'Content 1');
INSERT INTO page (title, content) VALUES ('Page 2', 'Content 2');
INSERT INTO page (title, content) VALUES ('Page 3', 'Content 3');

# Crearea testelor

Creați în rădăcina directorului containers08 directorul ./tests. În directorul creat creați fișierul testframework.php cu următorul conținut:

<?php

function message($type, $message) {
    $time = date('Y-m-d H:i:s');
    echo "{$time} [{$type}] {$message}" . PHP_EOL;
}

function info($message) {
    message('INFO', $message);
}

function error($message) {
    message('ERROR', $message);
}

function assertExpression($expression, $pass = 'Pass', $fail = 'Fail'): bool {
    if ($expression) {
        info($pass);
        return true;
    }
    error($fail);
    return false;
}

class TestFramework {
    private $tests = [];
    private $success = 0;

    public function add($name, $test) {
        $this->tests[$name] = $test;
    }

    public function run() {
        foreach ($this->tests as $name => $test) {
            info("Running test {$name}");
            if ($test()) {
                $this->success++;
            }
            info("End test {$name}");
        }
    }

    public function getResult() {
        return "{$this->success} / " . count($this->tests);
    }
}

Cream în directorul ./tests fișierul tests.php cu următorul conținut:

<?php

require_once __DIR__ . '/testframework.php';

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../modules/database.php';
require_once __DIR__ . '/../modules/page.php';

$testFramework = new TestFramework();

// test 1: check database connection
function testDbConnection() {
    global $config;
    // ...
}

// test 2: test count method
function testDbCount() {
    global $config;
    // ...
}

// test 3: test create method
function testDbCreate() {
    global $config;
    // ...
}

// test 4: test read method
function testDbRead() {
    global $config;
    // ...
}

// add tests
$tests->add('Database connection', 'testDbConnection');
$tests->add('table count', 'testDbCount');
$tests->add('data create', 'testDbCreate');
// ...

// run tests
$tests->run();

echo $tests->getResult();

Adăugam în fișierul ./tests/tests.php teste pentru toate metodele clasei Database, precum și pentru metodele clasei Page.

<?php

require_once __DIR__ . '/testframework.php';

$config = [
    "db" => [
        "path" => "/var/www/db/db.sqlite"
    ]
];

require_once '/var/www/html/modules/database.php';
require_once '/var/www/html/modules/page.php';

$tests = new TestFramework();

// test 1: check database connection
function testDbConnection() {
    global $config;
    $db = new Database($config["db"]["path"]);
    return assertExpression(true, "Database connection successful", "Failed to connect to database");
}

// test 2: test count method
function testDbCount() {
    global $config;
    $db = new Database($config["db"]["path"]);
    $count = $db->Count("page");
    return assertExpression($count == 3, "Count method returned correct value: $count", "Count method failed, expected 3 but got $count");
}

// test 3: test create method
function testDbCreate() {
    global $config;
    $db = new Database($config["db"]["path"]);
    $initialCount = $db->Count("page");
    
    $newId = $db->Create("page", [
        "title" => "Test Page",
        "content" => "Test Content"
    ]);
    
    $newCount = $db->Count("page");
    $record = $db->Read("page", $newId);
    
    $db->Delete("page", $newId); // Clean up
    
    return assertExpression(
        $newCount == $initialCount + 1 && $record["title"] == "Test Page" && $record["content"] == "Test Content",
        "Create method created a record successfully",
        "Create method failed"
    );
}

// test 4: test read method
function testDbRead() {
    global $config;
    $db = new Database($config["db"]["path"]);
    
    $record = $db->Read("page", 1);
    
    return assertExpression(
        $record && $record["id"] == 1 && $record["title"] == "Page 1" && $record["content"] == "Content 1",
        "Read method returned correct record",
        "Read method failed"
    );
}

// test 5: test update method
function testDbUpdate() {
    global $config;
    $db = new Database($config["db"]["path"]);
    
    $newId = $db->Create("page", [
        "title" => "Update Test",
        "content" => "Before Update"
    ]);
    
    $db->Update("page", $newId, [
        "title" => "Updated Title",
        "content" => "After Update"
    ]);
    
    $record = $db->Read("page", $newId);
    
    $db->Delete("page", $newId); // Clean up
    
    return assertExpression(
        $record["title"] == "Updated Title" && $record["content"] == "After Update",
        "Update method updated the record successfully",
        "Update method failed"
    );
}

// test 6: test delete method
function testDbDelete() {
    global $config;
    $db = new Database($config["db"]["path"]);
    
    $newId = $db->Create("page", [
        "title" => "Delete Test",
        "content" => "To Be Deleted"
    ]);
    
    $initialCount = $db->Count("page");
    $db->Delete("page", $newId);
    $newCount = $db->Count("page");
    
    return assertExpression(
        $newCount == $initialCount - 1,
        "Delete method deleted the record successfully",
        "Delete method failed"
    );
}

// test 7: test fetch method
function testDbFetch() {
    global $config;
    $db = new Database($config["db"]["path"]);
    
    $results = $db->Fetch("SELECT * FROM page WHERE id <= 3 ORDER BY id");
    
    return assertExpression(
        count($results) == 3 && 
        $results[0]["id"] == 1 && 
        $results[1]["id"] == 2 && 
        $results[2]["id"] == 3,
        "Fetch method returned correct results",
        "Fetch method failed"
    );
}

// test 8: test page rendering
function testPageRender() {
    $page = new Page('/var/www/html/templates/index.tpl');
    
    $data = [
        "title" => "Test Title",
        "content" => "Test Content"
    ];
    
    $rendered = $page->Render($data);
    
    return assertExpression(
        strpos($rendered, "Test Title") !== false && 
        strpos($rendered, "Test Content") !== false,
        "Page rendering works correctly",
        "Page rendering failed"
    );
}

// add tests
$tests->add('Database connection', 'testDbConnection');
$tests->add('Table count', 'testDbCount');
$tests->add('Data create', 'testDbCreate');
$tests->add('Data read', 'testDbRead');
$tests->add('Data update', 'testDbUpdate');
$tests->add('Data delete', 'testDbDelete');
$tests->add('Data fetch', 'testDbFetch');
$tests->add('Page render', 'testPageRender');

// run tests
$tests->run();

echo "Test results: " . $tests->getResult();

# Crearea Dockerfile

Cream în directorul rădăcină al proiectului fișierul Dockerfile cu următorul conținut:

FROM php:7.4-fpm as base

RUN apt-get update && \
    apt-get install -y sqlite3 libsqlite3-dev && \
    docker-php-ext-install pdo_sqlite

VOLUME ["/var/www/db"]

COPY sql/schema.sql /var/www/db/schema.sql

RUN echo "prepare database" && \
    cat /var/www/db/schema.sql | sqlite3 /var/www/db/db.sqlite && \
    chmod 777 /var/www/db/db.sqlite && \
    rm -rf /var/www/db/schema.sql && \
    echo "database is ready"

COPY site /var/www/html

# Configurarea Github Actions

Cream în directorul rădăcină al proiectului fișierul .github/workflows/main.yml cu următorul conținut:

name: CI

on:
  push:
    branches:
      - main

jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v4
      - name: Build the Docker image
        run: docker build -t containers08 .
      - name: Create `container`
        run: docker create --name container --volume database:/var/www/db containers08
      - name: Copy tests to the container
        run: docker cp ./tests container:/var/www/html
      - name: Up the container
        run: docker start container
      - name: Run tests
        run: docker exec container php /var/www/html/tests/tests.php
      - name: Stop the container
        run: docker stop container
      - name: Remove the container
        run: docker rm container

# Pornire și testare

![image](https://github.com/user-attachments/assets/9a966356-e5d0-4f20-983c-c71f48a35644)

Ce este integrarea continuă?
Integrarea continuă (Continuous Integration - CI) este o practică de dezvoltare software în care dezvoltatorii integrează frecvent (de obicei zilnic sau chiar de mai multe ori pe zi) modificările codului sursă într-un depozit partajat. Fiecare integrare este apoi verificată automat prin compilare și rularea testelor pentru a detecta rapid erorile și a preveni problemele de integrare.

2. Pentru ce sunt necesare testele unitare? Cât de des trebuie să fie executate?
Testele unitare sunt folosite pentru a verifica funcționarea corectă a celor mai mici componente ale aplicației (de exemplu, funcții sau metode individuale).

Sunt necesare pentru:

Detectarea timpurie a erorilor în cod.

Asigurarea că modificările aduse codului nu strică funcționalități existente.

Creșterea încrederii în calitatea codului și în modificările făcute.

Frecvență de execuție:

La fiecare modificare a codului (commit).

La fiecare pull request.

În timpul procesului de integrare continuă (CI).

Opțional, în timpul dezvoltării locale de către programatori.

3. Ce modificări trebuie făcute în fișierul .github/workflows/main.yml pentru a rula testele la fiecare solicitare de trage (Pull Request)?
Trebuie să adaugi (sau să modifici) secțiunea on pentru a include pull_request. De exemplu:

on:
  push:
    branches: [ main ]
  pull_request:
    branches: [ main ]

Ce trebuie adăugat în fișierul .github/workflows/main.yml pentru a șterge imaginile create după testare?
Dacă în timpul testării sunt create imagini Docker, acestea ar trebui șterse pentru a elibera spațiu. Poți adăuga un pas de tip run la finalul jobului care rulează testele:

- name: Șterge imaginile Docker
  run: docker image prune -f

# Conluzie 

Această lucrare a demonstrat importanța integrării continue prin GitHub Actions. 

Automatizarea testelor într-un mediu izolat (container Docker) asigură calitatea codului, reduce erorile și accelerează dezvoltarea. 

Configurația simplă și clară permite o extindere ușoară a procesului pentru proiecte mai complexe.
