<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\GameModule;

class InformationPresenter extends BaseGamePresenter
{
	public function actionDefault()
	{
		$this->template->informations = $this->informationRepository->findAllByGame($this->game);
	}

	public function actionDetail($id)
	{
		$this->template->informations = $this->informationRepository->findAllByGame($this->game);
		if(!$this->template->information = $this->informationRepository->findBySlug($id, $this->game)) {
			$this->flashError('Tato stránka již neexistuje.');
			$this->redirect('Dashboard:default');
		}
	}
}
