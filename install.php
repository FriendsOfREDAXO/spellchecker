<?php

rex_yform_manager_table::deleteCache();

/** @var rex_addon $this */

$content = rex_file::get(rex_path::addon('spellchecker', 'install/tablesets/yform_spellchecker_tables.json'));
rex_yform_manager_table_api::importTablesets($content);

rex_delete_cache();
rex_yform_manager_table::deleteCache();

rex_dir::create(rex_addon::get('spellchecker')->getDataPath());
