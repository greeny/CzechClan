<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\GameModule;

use Nette\Utils\Paginator;
use Tempeus\Controls\Form;
use Tempeus\Model\ForumFacade;
use Tempeus\Model\ForumThread;
use Tempeus\Model\ForumTopic;
use Tempeus\Model\RepositoryException;

class ForumPresenter extends BaseGamePresenter
{
	/** @var ForumFacade @inject */
	public $forumFacade;

	/** @var ForumTopic */
	protected $topic;

	/** @var ForumThread */
	protected $thread;

	public function renderDefault()
	{
		$this->template->topicId = NULL;
		$this->template->breadcrumbs = array();
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

	public function renderThread($id, $page = 1)
	{
		$this->template->thread = $this->thread = $this->forumFacade->getThread($id);
		$this->template->paginator = $paginator = new Paginator;
		$paginator->page = $page;
		$paginator->itemsPerPage = 25;
		$paginator->itemCount = $this->forumFacade->countPostsInThread($this->thread);
		$this->template->posts = $this->forumFacade->getPostsInThread($this->thread, $paginator);
		$this->template->breadcrumbs = $this->forumFacade->getBreadcrumbsForThread($this->thread);
	}

	public function actionCreateTopic($id = NULL)
	{
		if(!$this->authorizator->isAdminAllowed($this->user, $this->game->slug, 'forum')) {
			$this->flashError('Nemáš oprávnění k vytváření témat.');
			$this->redirect($id === NULL ? 'default' : 'topic', array('id' => $id));
		}
		if($id) {
			try {
				$topic = $this->forumFacade->getTopic($id);
				if(!$this->forumFacade->canUserSeeTopic($this->user, $topic)) {
					$this->flashError('Nemáš přístup k tomuto tématu.');
					$this->redirect('default');
				}
				$this->template->breadcrumbs = $this->forumFacade->getBreadcrumbsForTopic($topic);
			} catch(RepositoryException $e) {
				$this->flashError($e->getMessage());
				$this->redirect('default');
			}
		} else {
			$this->template->breadcrumbs = array();
		}
	}

	public function actionEditTopic($id)
	{
		try {
			$this->topic = $this->forumFacade->getTopic($id);
			if(!$this->forumFacade->canUserSeeTopic($this->user, $this->topic)) {
				$this->flashError('Nemáš přístup k tomuto tématu.');
				$this->redirect('default');
			}
			$this->template->breadcrumbs = $this->forumFacade->getBreadcrumbsForTopic($this->topic);
		} catch(RepositoryException $e) {
			$this->flashError($e->getMessage());
			$this->redirect('default');
		}
		if(!$this->authorizator->isAdminAllowed($this->user, $this->game->slug, 'forum')) {
			$this->flashError('Nemáš oprávnění k upravování témat.');
			$this->redirect('default');
		}
	}

	public function actionCreateThread($id)
	{
		if(!$this->user->isLoggedIn()) {
			$this->flashError('Pro přidávání vláken se prosím přihlaš.');
			$this->redirect('default');
		}
		try {
			$this->topic = $this->forumFacade->getTopic($id);
			if(!$this->forumFacade->canUserSeeTopic($this->user, $this->topic)) {
				$this->flashError('Nemáš přístup k tomuto tématu.');
				$this->redirect('default');
			}
			$this->template->breadcrumbs = $this->forumFacade->getBreadcrumbsForTopic($this->topic);
		} catch(RepositoryException $e) {
			$this->flashError($e->getMessage());
			$this->redirect('default');
		}
	}

	public function actionEditThread($id)
	{
		try {
			$this->template->thread = $this->thread = $this->forumFacade->getThread($id);
			if(!$this->forumFacade->canUserSeeTopic($this->user, $this->thread->topic)) {
				$this->flashError('Nemáš přístup k tomuto vláknu.');
				$this->redirect('default');
			}
			$this->template->breadcrumbs = $this->forumFacade->getBreadcrumbsForTopic($this->thread->topic);
		} catch(RepositoryException $e) {
			$this->flashError($e->getMessage());
			$this->redirect('default');
		}
	}

	public function actionCreatePost($id)
	{
		try {
			$this->template->thread = $this->thread = $this->forumFacade->getThread($id);
			if(!$this->forumFacade->canUserSeeTopic($this->user, $this->thread->topic)) {
				$this->flashError('Nemáš přístup k tomuto vláknu.');
				$this->redirect('default');
			}
			$this->template->breadcrumbs = $this->forumFacade->getBreadcrumbsForTopic($this->thread->topic);
		} catch(RepositoryException $e) {
			$this->flashError($e->getMessage());
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

	protected function createThreadForm()
	{
		$form = $this->createForm();
		$form->addText('title', 'Titulek')
			->setRequired('Prosím zadej titulek');
		return $form;
	}

	protected function createComponentCreateThreadForm()
	{
		$form = $this->createThreadForm();
		$form->addTextArea('text', 'Obsah')
			->setAttribute('class', 'ckeditor');
		$form->addSubmit('CreateThread', 'Vytvořit vlákno');
		$form->onSuccess[] = $this->CreateThreadFormSuccess;
		return $form;
	}

	public function CreateThreadFormSuccess(Form $form)
	{
		$v = $form->getValues();
		$thread = $this->forumFacade->createThread($this->userRepository->find($this->user->id), $this->topic, $v);
		$this->flashSuccess('Vlákno bylo vytvořeno.');
		$this->redirect('thread', array('id' => $thread->id));
	}

	protected function createComponentEditThreadForm()
	{
		$form = $this->createThreadForm();
		$form->setDefaults($this->thread->getData(array('title')));
		$form->addSubmit('editThread', 'Upravit titulek');
		$form->onSuccess[] = $this->editThreadFormSuccess;
		return $form;
	}

	public function editThreadFormSuccess(Form $form)
	{
		$v = $form->getValues();
		try {
			$thread = $this->forumFacade->getThread($this->params['id']);
			$this->forumFacade->updateThread($thread, $v);
			$this->flashSuccess('Vlákno bylo upraveno');
			$this->redirect('topic', array('id' => $thread->topic->id));
		} catch(RepositoryException $e) {
			$this->flashError($e->getMessage());
			$this->redirect('default');
		}
	}

	protected function createPostForm()
	{
		$form = $this->createForm();
		$form->addText('title', 'Titulek')
			->setRequired('');
		$form->addTextArea('text', 'Text')
			->setAttribute('class', 'ckeditor');
		return $form;
	}

	protected function createComponentCreatePostForm()
	{
		$form = $this->createPostForm();
		$form->setDefaults(array('title' => 'Re: ' . $this->thread->title));
		$form->addSubmit('createPost', 'Přidat příspěvek');
		$form->onSuccess[] = $this->createPostFormSuccess;
		return $form;
	}

	public function createPostFormSuccess(Form $form)
	{
		$v = $form->getValues();
		$post = $this->forumFacade->createPost($this->userRepository->find($this->user->id), $this->thread, $v);
		$this->flashSuccess('Příspěvek byl přidán');
		$this->redirect('thread#post-'.$post->order, array('id' => $this->thread->id, 'page' => $post->page));
	}
}
