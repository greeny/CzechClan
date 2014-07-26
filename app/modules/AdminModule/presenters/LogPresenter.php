<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\AdminModule;

class LogPresenter extends BaseGeneralAdminPresenter
{

	public function renderDefault()
	{
		$this->template->logs = $this->logRepository->findAll();
	}

	protected function checkPermissions()
	{
		return $this->isAllowed('log');
	}
}
