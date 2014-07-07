<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\UserModule;

use CzechClan\BasePresenter;
use CzechClan\Model\UserRepository;

class BaseUserPresenter extends BasePresenter
{
	/** @var UserRepository @inject */
	public $userRepository;
}
