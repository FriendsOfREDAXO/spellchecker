<?php

/** @var rex_addon $this */

$target_page = rex_request('page', 'string');
$wrapper = '';
$show_title = false;

if ('yform/manager/data_edit' === $target_page) {
    $table_name = rex_request('table_name', 'string');
    $show_title = true;
} elseif (isset($this->getProperty('page')['subpages'][rex_be_controller::getCurrentPagePart(2)])) {
    // page-Properties allgemein abrufen
    $properties = $this->getProperty('page')['subpages'][rex_be_controller::getCurrentPagePart(2)];
    $sub = rex_be_controller::getCurrentPagePart(3);
    if (null !== $sub) {
        $properties = $properties['subpages'][$sub];
    }
    // yform-properties
    $table_name = $properties['yformTable'] ?? '';
    $wrapper = $properties['yformClass'] ?? '';
    $show_title = isset($properties['yformTitle']) && true === (bool) $properties['yformTitle'];
} else {
    $table_name = '';
}

$table = rex_yform_manager_table::get($table_name);

/** @var rex_yform_manager_table_perm_edit $complex_perm */
/** @phpstan-ignore-next-line */
$complex_perm = rex::getUser()->getComplexPerm('yform_manager_table_perm_edit');

if (null !== $table && null !== rex::getUser() && (rex::getUser()->isAdmin() || $complex_perm->hasPerm($table->getTableName()))) {
    try {
        $page = new rex_yform_manager();
        $page->setTable($table);
        $page->setLinkVars(['page' => $target_page, 'table_name' => $table->getTableName()]);

        if ('' !== $wrapper) {
            echo "<div class=\"$wrapper\">";
        }

        if ($show_title) {
            echo $page->getDataPage();
        } else {
            // Seite erzeugen und abfangen
            ob_start();
            echo $page->getDataPage();
            $page = ob_get_clean();
            // Such den Header - Fall 1: mit Suchspalte?
            $p = strpos($page, '</header>'.PHP_EOL.'<div class="row">');
            // Such den Header - Fall 2: ohne Suchspalte
            if (false === $p) {
                $p = strpos($page, '</header>'.PHP_EOL.'<section class="rex-page-section">');
            }
            // Header rauswerfen
            if (false !== $p) {
                $page = substr($page, $p);
            }
            // ausgabe
            echo $page;
        }

        if ('' !== $wrapper) {
            echo '</div>';
        }
    } catch (Exception $e) {
        $message = nl2br($e->getMessage()."\n".$e->getTraceAsString());
        echo rex_view::warning($message);
    }
} elseif (null === $table) {
    echo rex_view::warning(rex_i18n::msg('yform_table_not_found'));
}
