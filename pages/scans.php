<?php

declare(strict_types=1);

$content = '';
$scans = rex_spellchecker_scan::getScans();

if (0 == count($scans)) {
    echo rex_view::info(rex_i18n::msg('spellchecker_noscansfound'));
} else {
    $content = '<table class="table table-hover">';
    $content .= '<thead>
            <th>'.rex_i18n::msg('spellchecker_scankey').'</th>
            <th>'.rex_i18n::msg('spellchecker_language').'</th>
            <th>'.rex_i18n::msg('spellchecker_table').'</th>
            <th>'.rex_i18n::msg('spellchecker_fieldsinfo').'</th>
            <th>'.rex_i18n::msg('spellchecker_scan_query').'</th>
            <th>'.rex_i18n::msg('spellchecker_scan_query_count').'</th>
            <th>'.rex_i18n::msg('spellchecker_scan_link').'</th>
            <th>'.rex_i18n::msg('spellchecker_scan_interval').'</th>
            <th>'.rex_i18n::msg('spellchecker_scan_duration').'</th>
            <th>'.rex_i18n::msg('spellchecker_getCurrentScanPosition').'</th>
            <th>'.rex_i18n::msg('spellchecker_scanDone').'</th>
            </thead>';
    $content .= '<tbody>';
    foreach ($scans as $scan) {
        /* @var rex_spellchecker_scan $scan */

        $content .= '<tr class="rex">';
        $content .= '<td><b>'.rex_escape($scan->getKey()).'</b></td>'; // <br /><a href="'.rex_url::currentBackendPage(['func' => 'add_word', 'word_id' => 1]).'">' . rex_i18n::msg('spellchecker_add_word'). '</a>
        $content .= '<td>'.rex_escape($scan->getLanguage()).'</td>';
        $content .= '<td>'.rex_escape($scan->getTableName()).'</td>';
        $content .= '<td>ID: `'.rex_escape($scan->getIdField()).'`
                    <br />Title: `'.rex_escape($scan->getTitleField()).'`
                    <br />ScanFields: `'.rex_escape(implode(', ', $scan->getScanFields())).'`
                </td>';

        $query = $scan->getQuery();
        if (strlen($query) > 15) {
            $query = substr($scan->getQuery(), 0, 7).' ... '.substr($scan->getQuery(), -7);
        }
        $content .= '<td><span title="'.rex_escape($scan->getQuery()).'">'.rex_escape($query).'</span></td>';

        $queryCount = $scan->getQueryCount();
        if (strlen($queryCount) > 15) {
            $queryCount = substr($scan->getQueryCount(), 0, 7).' ... '.substr($scan->getQueryCount(), -7);
        }
        $content .= '<td><span title="'.rex_escape($scan->getQueryCount()).'">'.rex_escape($queryCount).'</span></td>';

        $link = $scan->getLink();
        if (strlen($queryCount) > 15) {
            $link = substr($scan->getLink(), 0, 7).' ... '.substr($scan->getLink(), -7);
        }
        $content .= '<td><span title="'.rex_escape($scan->getLink()).'">'.rex_escape($link).'</span></td>';

        $content .= '<td>'.rex_escape($scan->getInterval()).'</td>';
        $content .= '<td>'.rex_escape($scan->getDuration()).'</td>';
        $content .= '<td>'.rex_escape($scan->getCurrentScanPosition()).'</td>';
        $content .= '<td>'.rex_escape(($scan->scanDone()) ? 'yes' : 'no').'</td>';
        $content .= '</tr>';
    }
    $content .= '</tbody>';
    $content .= '</table>';
    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('spellchecker_title_scans'));
    $fragment->setVar('body', $content, false);
    echo $fragment->parse('core/page/section.php');
}
