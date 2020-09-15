<?php

declare(strict_types=1);

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class rex_spellchecker_issue extends \rex_yform_manager_dataset
{
    public static function addIssue($wordObject, rex_spellchecker_scan $scan, $item, $issue): void
    {
        // $issue has further attributes: suggestions and offset .. just in case
        // is issue exists -> save will fail because of yform validation
        self::create()
            ->setValue('word_id', $wordObject->getId())
            ->setValue('ignore', 0)
            ->setValue('scankey', $scan->getKey())
            ->setValue('item_id', $item[$scan->getIdField()])
            ->setValue('createdate', date('Y-m-d').' 00:00:00')
            ->save();
    }

    public static function deleteItemIssues($scan, $item): void
    {
        foreach (self::query()
                    ->where('scankey', $scan->getKey())
                    ->where('ignore', 0)
                    ->where('item_id', $item[$scan->getIdField()])
                    ->find()
                as $issue) {
            $issue->delete();
        }
    }

    public static function deleteIssuesByWordId($word_id): void
    {
        foreach (self::query()
                     ->where('word_id', $word_id)
                     ->where('ignore', 0)
                     ->find()
                 as $issue) {
            $issue->delete();
        }
        $cache = new FilesystemAdapter();
        $cache->delete('spellchecker_getWordsCount');
    }

    public static function getWordsCount(int $limit = 20)
    {
        $cache = new FilesystemAdapter();

        return $cache->get('spellchecker_getWordsCount', function (ItemInterface $item) use ($limit) {
            $item->expiresAfter(3600);

            return rex_sql::factory()->getArray('select count(id) as counts, word_id from '.self::table().' where `ignore`=0 group by word_id order by counts desc LIMIT '.$limit);
        });
    }

    public static function getItemsByWordId(int $word_id, $limit = 20)
    {
        return rex_sql::factory()->getArray('select * from '.self::table().' where `ignore`=0 and word_id = ? LIMIT '.$limit, [$word_id]);
    }
}
