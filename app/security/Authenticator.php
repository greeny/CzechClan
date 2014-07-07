<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\Security;

use Nette\Object;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
use Nette\Security\IIdentity;
use CzechClan\Model\UserRepository;

class Authenticator extends Object implements IAuthenticator {

	/** @var UserRepository */
	protected $userRepository;

	public function __construct(UserRepository $userRepository)
	{
		$this->userRepository = $userRepository;
	}

	/**
	 * @param array $credentials
	 * @throws \Nette\Security\AuthenticationException
	 * @return IIdentity|void
	 */
	function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;

		if(!$user = $this->userRepository->findByNick($username)) {
			throw new AuthenticationException("Uživatel '$username' nenalezen.", self::IDENTITY_NOT_FOUND);
		}

		if(PasswordHasher::hashPassword($username, $password, $user->salt) !== $user->password) {
			throw new AuthenticationException("Špatné heslo.", self::INVALID_CREDENTIAL);
		}

		if($user->verified === FALSE) {
			throw new AuthenticationException("Uživatel nemá ověřený email.", self::NOT_APPROVED);
		}

		return new Identity($user->id, $user->role, $user->getData());
	}
}
