<?php

/**
 * PHP Speller.
 *
 * @copyright 2017, Михаил Красильников <m.krasilnikov@yandex.ru>
 * @author    Михаил Красильников <m.krasilnikov@yandex.ru>
 * @license   http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace Mekras\Speller\Exception;

/**
 * Runtime exception.
 *
 * @since 1.6
 */
class RuntimeException extends \RuntimeException implements PhpSpellerException
{
}
