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
