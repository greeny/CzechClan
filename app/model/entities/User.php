<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

use Tempeus\Security\PasswordHasher;

/**
 * @property-read int $id
 * @property string $nick
 * @property string $password
 * @property string $email
 * @property string $salt
 * @property string $role
 * @property bool $verified = FALSE
 * @property Role[] $roles m:hasMany
 */
class User extends BaseEntity
{
	public function fixPassword($password)
	{
		$this->password = PasswordHasher::hashPassword($this->nick, $password, $this->salt);
	}
}
