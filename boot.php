<?php

declare(strict_types=1);

rex_yform_manager_dataset::setModelClass('rex_spellchecker_dictionary', rex_spellchecker_dictionary::class);
rex_yform_manager_dataset::setModelClass('rex_spellchecker_issue', rex_spellchecker_issue::class);

rex_extension::register(
    ['YFORM_DATA_UPDATED', 'YFORM_DATA_ADDED'],
    static function ($ep): void {
        $params = $ep->getParams();
        $table = $params['table'];
        if ('rex_spellchecker_dictionary' == $table->getTableName()) {
            $data = $params['data']->getData();
            if (1 == $data['dic']) {
                dump($data);
                rex_spellchecker_issue::deleteIssuesByWordId($data['id']);
            }
            rex_spellchecker::compileDictionaries();
        }
    });
