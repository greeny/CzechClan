<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

/**
 * @property-read int $id
 * @property Game $game m:hasOne
 * @property string $title
 * @property string $slug
 * @property string $content
 * @property int $order
 * @property bool $active
 */
class Information extends BaseEntity
{

}
