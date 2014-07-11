<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\Model;

use Nette\Utils\ArrayHash;
use Nette\Security\AuthenticationException;
use Nette\Utils\Random;
use CzechClan\Security\PasswordHasher;

class UserRepository extends BaseRepository {

	public function findByNick($nick)
	{
		$row = $this->connection->select('*')
			->from($this->getTable())
			->where('[nick] = %s', $nick)
			->fetch();
		return $row ? $this->createEntity($row) : NULL;
	}

	public function findByEmail($email)
	{
		$row = $this->connection->select('*')
			->from($this->getTable())
			->where('[email] = %s', $email)
			->fetch();
		return $row ? $this->createEntity($row) : NULL;
	}

	/**
	 * @param ArrayHash $data
	 * @return User
	 * @throws \Nette\Security\AuthenticationException
	 */
	public function register(ArrayHash $data)
	{
		if($this->findByNick($data->nick)) {
			throw new AuthenticationException("Uživatel '$data->nick' již existuje.");
		}
		if($u = $this->findByEmail($data->email)) {
			throw new AuthenticationException("Uživatel s emailem '$data->email' již existuje ('$u->nick').");
		}

		$user = User::from($data);
		$user->salt = Random::generate(5, 'A-Za-z0-9');
		$user->role = 'member';
		$user->fixPassword($user->password);
		$this->persist($user);
		return $user;
	}

	public function findPairs()
	{
		return $this->connection->select('*')
			->from($this->getTable())
			->orderBy('[nick] ASC')
			->fetchPairs('id', 'nick');
	}

}
