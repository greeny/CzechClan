<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

/**
 * @property-read int $id
 * @property Game $game m:hasOne
 * @property User[] $users m:hasMany
 * @property string $name
 * @property string $description
 * @property int $order
 */
class Team extends BaseEntity
{

}
