<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\Model;

use CzechClan\Security\PasswordHasher;

/**
 * @property-read int $id
 * @property string $nick
 * @property string $password
 * @property string $email
 * @property string $salt
 * @property string $role
 * @property bool $verified = FALSE
 */
class User extends BaseEntity
{
	public function fixPassword($password)
	{
		$this->password = PasswordHasher::hashPassword($this->nick, $password, $this->salt);
	}
}
