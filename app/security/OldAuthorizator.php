<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\Security;

use Nette\Security\Permission;

class OldAuthorizator extends Permission {


	public function __construct()
	{
		$this->addRole('guest');
		$this->addRole('member', 'guest');
		$this->addRole('admin', 'member');
		$this->addRole('owner', 'admin');

		$this->addResource('forum');
		$this->addResource('shoutbox');
		$this->addResource('admin');

		$this->allow('guest', array('forum', 'shoutbox'), array('view'));
		$this->allow('member', array('forum', 'shoutbox'), array('add', 'edit', 'delete'));
		$this->allow('admin', 'admin', 'access');

		$this->allow('owner');
	}
}
