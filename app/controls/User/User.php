<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\Controls\User;

use Nette\Application\UI\Control;

class User extends Control
{
	public function render($user)
	{
		$this->template->setFile(__DIR__.'/user.latte');
		$this->template->u = $user;
		$this->template->render();
	}
}
 