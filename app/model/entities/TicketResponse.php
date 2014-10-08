<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

/**
 * @property-read int $id
 * @property Ticket $ticket m:hasOne
 * @property User $user m:hasOne
 * @property int $time
 * @property string $text
 */
class TicketResponse extends BaseEntity
{

}
