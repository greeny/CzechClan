<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

/**
 * @property-read int $id
 * @property string $name
 * @property bool $public
 * @property User[] $allowedUsers m:hasMany
 */
class ChatRoom extends BaseEntity
{

}
