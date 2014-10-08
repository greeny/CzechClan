<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

/**
 * @property-read int $id
 * @property TicketResponse[] $ticketResponses m:belongsToMany
 * @property User $user m:hasOne
 * @property User|NULL $assignedUser m:hasOne(assigned_user_id)
 * @property Game|NULL $game m:hasOne
 * @property bool $public
 * @property int $priority
 * @property int $status
 * @property string $title
 * @property string $text
 * @property int $dateCreated (date_created)
 */
class Ticket extends BaseEntity
{
	const STATUS_WAITING = 0;
	const STATUS_REJECTED = 1;
	const STATUS_FINISHED = 2;

	public function getStatusMessage()
	{
		return $this::getStatusMessageFor($this->status);
	}

	public static function getStatusMessageFor($status)
	{
		static $messages = [
			self::STATUS_WAITING => 'Čeká na vyřízení',
			self::STATUS_REJECTED => 'Zamítnuto',
			self::STATUS_FINISHED => 'Vyřízeno',
		];
		return $messages[$status];
	}

	public static function getStatusMessages()
	{
		$arr = [];
		foreach([self::STATUS_WAITING, self::STATUS_REJECTED, self::STATUS_FINISHED] as $status) {
			$arr[$status] = self::getStatusMessageFor($status);
		}
		return $arr;
	}

	public function getResponses()
	{
		$responses = $this->ticketResponses;
		usort($responses, function(TicketResponse $response1, TicketResponse $response2) {
			return $response1->time > $response2->time;
		});
		return $responses;
	}
}
