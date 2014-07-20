<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\GameModule;

class InformationPresenter extends BaseGamePresenter
{
	public function actionDetail($id)
	{
		if(!$this->template->information = $this->informationRepository->findBySlug($id, $this->game)) {
			$this->flashError('Tato stránka již neexistuje.');
			$this->redirect('Dashboard:default');
		}
	}
}
