<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

/**
 * @property-read int $id
 * @property string $key
 * @property User $user m:hasOne
 * @property int $dateStarted (date_started)
 * @property int $dateChecked (date_checked)
 * @property int|NULL $dateEnded (date_ended)
 */
class ChatSession extends BaseEntity
{

}
