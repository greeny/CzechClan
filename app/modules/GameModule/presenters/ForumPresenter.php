<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\GameModule;

use Tempeus\Controls\Form;
use Tempeus\Model\ForumFacade;
use Tempeus\Model\ForumTopic;
use Tempeus\Model\RepositoryException;

class ForumPresenter extends BaseGamePresenter
{
	/** @var ForumFacade @inject */
	public $forumFacade;

	/** @var ForumTopic */
	protected $topic;

	public function renderDefault()
	{
		$this->template->topicId = NULL;
		$this->template->topics = $this->forumFacade->getTopics($this->game, $this->user);
	}

	public function renderTopic($id)
	{
		try {
			$this->setView('default');
			$this->template->topicId = $id;
			$topic = $this->forumFacade->getTopic($id);
			$this->template->topics = $this->forumFacade->getTopics($this->game, $this->user, $topic);
			$this->template->breadcrumbs = $this->forumFacade->getBreadcrumbsForTopic($topic);
			$this->template->threads = $this->forumFacade->getThreads($topic);
		} catch(RepositoryException $e) {
			$this->flashError($e->getMessage());
			$this->redirect('default');
		}
	}

	public function renderThread($id)
	{
		$this->template->thread = $thread = $this->forumFacade->getThread($id);
		$this->template->posts = $this->forumFacade->getPostsInThread($thread);
		$this->template->breadcrumbs = $this->forumFacade->getBreadcrumbsForThread($thread);
	}

	public function actionCreateTopic($id = NULL)
	{
		if(!$this->authorizator->isAdminAllowed($this->user, $this->game->slug, 'forum')) {
			$this->flashError('Nemáš oprávnění k vytváření témat.');
			$this->redirect($id === NULL ? 'default' : 'topic', array('id' => $id));
		}
	}

	public function actionEditTopic($id)
	{
		try {
			$this->topic = $this->forumFacade->getTopic($id);
		} catch(RepositoryException $e) {
			$this->flashError($e->getMessage());
			$this->redirect('default');
		}
		if(!$this->authorizator->isAdminAllowed($this->user, $this->game->slug, 'forum')) {
			$this->flashError('Nemáš oprávnění k upravování témat.');
			$this->redirect('default');
		}
	}

	public function handleMoveTopic($topicId, $direction)
	{
		try {
			$topic = $this->forumFacade->getTopic($topicId);
			$this->forumFacade->moveTopic($topic, $direction);
		} catch(RepositoryException $e) {
			$this->flashError($e->getMessage());
		}
		$this->refresh();
	}

	public function handleDeleteTopic($topicId)
	{

	}

	protected function createTopicForm()
	{
		$form = $this->createForm();
		$form->addText('title', 'Název')
			->setRequired('Prosím zadej název');
		$form->addText('subtitle', 'Popis')
			->setRequired('Prosím zadej popis');
		$form->addCheckbox('public', 'Veřejné téma')
			->setDefaultValue(TRUE);
		return $form;
	}

	protected function createComponentCreateTopicForm()
	{
		$form = $this->createTopicForm();
		$form->addSubmit('createTopic', 'Vytvořit téma');
		$form->onSuccess[] = $this->createTopicFormSuccess;
		return $form;
	}

	public function createTopicFormSuccess(Form $form)
	{
		$v = $form->getValues();
		try {
			$topic = $this->forumFacade->createTopic($this->game, $v, $this->params['id']);
			$this->flashSuccess('Téma bylo vytvořeno.');
			$this->redirect('editTopic', array('id' => $topic->id));
		} catch(RepositoryException $e) {
			$this->flashError($e->getMessage());
			$this->refresh();
		}
	}

	protected function createComponentEditTopicForm()
	{
		$form = $this->createTopicForm();
		$form->setDefaults($this->topic->getData(array('title', 'subtitle', 'public')));
		$form->addSubmit('editTopic', 'Upravit téma');
		$form->onSuccess[] = $this->editTopicFormSuccess;
		return $form;
	}

	public function editTopicFormSuccess(Form $form)
	{
		$v = $form->getValues();
		$this->forumFacade->updateTopic($this->topic, $v);
		$this->flashSuccess('Téma bylo upraveno');
		$this->refresh();
	}
}
