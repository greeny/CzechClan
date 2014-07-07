<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\Mail;

use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

class CzechClanMailer extends SendmailMailer
{
	protected $fromEmail;

	protected $fromName;

	public function __construct($fromEmail, $fromName)
	{
		$this->fromEmail = $fromEmail;
		$this->fromName = $fromName;
	}

	public function send(Message $mail)
	{
		$mail->setFrom($this->fromEmail, $this->fromName);
	}
}
