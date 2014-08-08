<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\ApiModule;

class PingPresenter extends BaseApiPresenter
{
	public function actionDefault()
	{
		$this->data->users = $this->entityFactory->createUsersFromSessions($this->sessionRepository->findActiveOnChat());
	}
}
