<?php

/**
 * Cronjob Addon.
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo\cronjob
 */

class rex_cronjob_spellchecker_scan extends rex_cronjob
{
    public function execute()
    {
        try {
            rex_spellchecker_scan::scan();
            $this->setMessage('Spellchecker Scan erfolgreich durchgeführt.');
            return true;
        } catch (rex_exception $e) {
            $this->setMessage($e->getMessage());
            return false;
        }
    }

    public function getTypeName()
    {
        return rex_i18n::msg('spellchecker_cronjob_scan');
    }

    public function getParamFields()
    {
        return [];
    }
}
