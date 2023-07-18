<?php

$content = '

<h3>Was macht der Spellchecker</h3>

Spellchecker ist dazu da Textpassagen in YForm Tabellen in der Rechtschreibung zu überprüfen.

Es können mehrere Tabellen und Felder ausgewählt werden und mit einer Bibliothek hinterlegt werden, um diese dann zu scannen und fehlerhafte Schreibweisen aufzulisten, um sie dann zu korrigieren.

Dazu ist aspell auf dem Server nötig. Der entsprechende Pfad kann in den AddOn-Settings gesetzt werden.

Weiterhin kann man die SystemSprachbibliotheken um eigene Wörter ergänzen oder auch bestimmte Fälle ignorieren.

<h3>Voraussetzungen</h3>

<p>aspell muss auf dem Server installiert sein, wie auch aspell-de für die deutsche Sprache.</p>


<h3>Hiermit wird ein Scan gestartet, welcher aber nur einen Teil scannt. Am besten diese Aufruf über die Cronjobs regelmäßig tätigen</h3>
<pre>
rex_spellchecker_scan::scan()
</pre>

<h3>in die boot.php des project-addOns</h3>
<p>Hier wird definiert, wie ein Scan geschehen soll, also welche Felder abgefragt werden und welche Datensätze verwendet weden sollen, wie auch, in welcher Sprache das pasieren soll</p>
<pre>
if (!rex::isFrontend() && class_exists(\'rex_spellchecker_scan\')) {

    $scan = new rex_spellchecker_scan(
        \'kennung\', // jeder scan bekommt eine eigene Kennung
        \'id\', // Id Feld
        \'title\', // title der angezeigt werden soll
        \'tablename\', // Tabellename
        [\'title\',\'teaser\',\'body\'], // Felder die gescannt werden sollen
        \'select id, title, teaser, body from tablename where language="de"\', // Abfrage Query der Tabelle
        \'select count(*) as count from tablename where language="de"\', // Count Query der Tabelle
        \'/redaxo/index.php?page=yform/manager/data_edit&table_name=tablename&rex_yform_manager_popup=0&data_id={id}&func=edit\', // Edit Link
        \'de\' // Bibliothek die verwendet werden soll
    );

    rex_spellchecker_scan::addScan($scan);
</pre>
';

$fragment = new rex_fragment();
$fragment->setVar('class', '');
$fragment->setVar('title', $this->i18n('spellchecker_description'));
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');




