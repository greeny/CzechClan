<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\PublicModule;

use CzechClan\BasePresenter;

class BasePublicPresenter extends BasePresenter {

	/** @var string @persistent */
	public $slug;

	public function startup()
	{
		parent::startup();
		// find by slug
	}

}
