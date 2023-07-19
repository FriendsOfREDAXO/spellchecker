<?php

declare(strict_types=1);

class rex_spellchecker
{
    public static function getDictionaryPath($language)
    {
        return rex_addon::get('spellchecker')->getDataPath($language.'_utf8.dic');
    }

    public static function compileDictionaries(): void
    {
        foreach (['de', 'en'] as $language) {
            $content = 'personal_ws-1.1 '.$language.' 0 utf-8';
            foreach (rex_spellchecker_dictionary::query()->where('language', $language)->where('dic', '1')->find() as $word) {
                $content .= "\n".$word->getValue('word');
            }
            rex_file::put(self::getDictionaryPath($language), $content);
        }
    }

    /*
    public static function convertDictionary()
    {
        $dic = rex_file::get(rex_addon::get('spellchecker')->getDataPath('dictionaries/variants.dic'));

        $encoding = mb_detect_encoding($dic, mb_list_encodings(), true);
        $dic = mb_convert_encoding($dic, "UTF-8", $encoding);

        rex_file::put(rex_addon::get('spellchecker')->getDataPath('dictionaries/variants_utf8.dic'), $dic);
    }
    */
}
