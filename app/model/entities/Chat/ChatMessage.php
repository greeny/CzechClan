<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

/**
 * @property-read int $id
 * @property ChatRoom|NULL $room m:hasOne
 * @property User $user m:hasOne
 * @property string $message
 * @property int $time
 */
class ChatMessage extends BaseEntity
{

}
