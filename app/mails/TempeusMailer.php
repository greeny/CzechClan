<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Mail;

use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

class TempeusMailer extends SendmailMailer
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
		parent::send($mail);
	}
}
