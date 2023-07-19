<?php

/** @var rex_addon $this */

$func = rex_request('func', 'string', '');
$word_id = rex_request('word_id', 'int', 0);
$issue_id = rex_request('issue_id', 'int', 0);
$mainContent = [];

switch ($func) {
    case 'ignore':
        $issue = rex_spellchecker_issue::get($issue_id);
        if (null !== $issue) {
            $issue->setValue('ignore', 1)->save();
            $func = 'open';
            $word_id = (int) $issue->getValue('word_id');
            echo rex_view::success(rex_i18n::msg('spellchecker_issue_ignored'));
        } else {
            echo rex_view::success(rex_i18n::msg('spellchecker_issue_not_found'));
        }

        break;
    case 'scan':
        rex_spellchecker_scan::scan();
        echo rex_view::success('spellchecker_scan_executed');

        break;
    case 'add_word':
        try {
            $word = rex_spellchecker_dictionary::get($word_id);
            $word->setValue('dic', 1)
                ->save();
            rex_spellchecker_issue::deleteIssuesByWordId($word_id);
            rex_spellchecker::compileDictionaries();
            echo rex_view::success($this->i18n('spellchecker_word_added', $word->getWord()));
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        break;
}

$content = '';
$words = rex_spellchecker_issue::getWordsCount(100);

$content = '<table class="table table-hover">';
$content .= '<thead>
            <th>'.rex_i18n::msg('spellchecker_word').'</th>
            <th>'.rex_i18n::msg('spellchecker_language').'</th>
            <th>'.rex_i18n::msg('spellchecker_count_about').'</th>
            </thead>';
$content .= '<tbody>';
foreach ($words as $word) {
    try {
        $wordObject = rex_spellchecker_dictionary::get($word['word_id']);
        if ((int) $word['word_id'] === $word_id) {
            $content .= '<tr class="rex">';
        } else {
            $content .= '<tr>';
        }
        $content .= '<td><b>'.rex_escape($wordObject->getWord()).'</b><br /><a href="'.rex_url::currentBackendPage(['func' => 'add_word', 'word_id' => $wordObject->getId()]).'">'.rex_i18n::msg('spellchecker_add_word').'</a></td>';
        $content .= '<td>'.$wordObject->getLanguage().'</td>';
        $content .= '<td>'.$word['counts'].'<br /><a href="'.rex_url::currentBackendPage(['func' => 'open', 'word_id' => $wordObject->getId()]).'">'.rex_i18n::msg('spellchecker_show_items').'</a></td>';
        $content .= '</tr>';
    } catch (Exception $e) {
        $content .= '<tr><td colspan="3">Word with ID '.$word['word_id'].' not found</td></tr>';
    } catch (Error $e) {
        $content .= '<tr><td colspan="3">Word with ID '.$word['word_id'].' not found</td></tr>';
    }
}
$content .= '</tbody>';
$content .= '</table>';
$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('spellchecker_title_words', count($words)), false);
$fragment->setVar('body', $content, false);
$mainContent[] = $fragment->parse('core/page/section.php');

$sideContent = [];

$fragment_title = '...';
$content = '';
if ('open' === $func) {
    $content = '<table class="table table-hover">';
    $content .= '<thead>
            <th>'.rex_i18n::msg('spellchecker_field_title').'</th>
            <th>'.rex_i18n::msg('spellchecker_field_id').'</th>
            <th>'.rex_i18n::msg('spellchecker_item').'</th>
            </thead>';

    $scans = rex_spellchecker_scan::getScans();
    $items = rex_spellchecker_issue::getItemsByWordId($word_id, 50);
    $word = rex_spellchecker_dictionary::get($word_id);

    $fragment_title = rex_i18n::msg('spellchecker_title_item_by_word', count($items), $word->getWord());

    $content .= '<tbody>';
    foreach ($items as $item) {
        if (isset($scans[$item['scankey']])) {
            /** @var rex_spellchecker_scan $scan */
            $scan = $scans[$item['scankey']];
            /** @phpstan-ignore-next-line */
            $title = rex_sql::factory()->getArray(
                    'select ' . rex_sql::factory()->escapeIdentifier($scan->getTitleField()) . ' from ' . rex_sql::factory()->escapeIdentifier($scan->getTableName()) . ' where ' . rex_sql::factory()->escapeIdentifier($scan->getIdField()) . ' = :item_id',
                    [
                        'item_id' => $item['item_id']
                    ]
            );
            if (count($title) > 0) {
                $link_edit = $scan->getLink();
                $link_edit = str_replace('{id}', $item['item_id'], $link_edit);
                $link_item_ignore = rex_url::currentBackendPage(['func' => 'ignore', 'issue_id' => $item['id']]);
                $content .= '
                    <tr>
                    <td>'.rex_escape($title[0]['title']).'</td>
                    <td>'.$item['item_id'].'</td>
                    <td><a href="'.$link_edit.'"><nobr>'.rex_i18n::msg('spellchecker_edit_item').'</nobr></a>
                    <br />'.rex_i18n::msg('or').'<br /><a href="'.$link_item_ignore.'"><nobr>'.rex_i18n::msg('spellchecker_ignore').'</nobr></a></td>
                    </tr>';
            }
        }
    }
    $content .= '</tbody>';
    $content .= '</table>';
}

$fragment = new rex_fragment();
$fragment->setVar('title', $fragment_title, false);
$fragment->setVar('body', $content, false);
$sideContent[] = $fragment->parse('core/page/section.php');

// ---------------------- Fragmente

$fragment = new rex_fragment();
$fragment->setVar('content', [implode('', $mainContent), implode('', $sideContent)], false);
$fragment->setVar('classes', ['col-lg-6', 'col-lg-6'], false);
echo $fragment->parse('core/page/grid.php');
