<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\ChatModule;

use Tempeus\BasePresenter;

abstract class BaseChatPresenter extends BasePresenter
{
	public function formatLayoutTemplateFiles() {
		return array();
	}
}
