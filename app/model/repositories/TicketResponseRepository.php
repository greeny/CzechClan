<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

class TicketResponseRepository extends BaseRepository
{
	public function addResponse(Ticket $ticket, User $user, $text)
	{
		$response = new TicketResponse();
		$response->ticket = $ticket;
		$response->user = $user;
		$response->text = $text;
		$response->time = time();
		$this->persist($response);
		return $response;
	}
}
