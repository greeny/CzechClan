<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\WebModule;

use Nette\Utils\Html;
use Tempeus\Controls\Form;
use Tempeus\Model\Ticket;
use Tempeus\Model\TicketRepository;
use Tempeus\Model\TicketResponseRepository;

class TicketPresenter extends BaseWebPresenter
{

	/** @var TicketRepository @inject */
	public $ticketRepository;

	/** @var TicketResponseRepository @inject */
	public $ticketResponseRepository;

	/** @var Ticket */
	protected $ticket;

	public function renderDefault()
	{
		$this->template->tickets = $this->ticketRepository->findPublicOpen();
	}

	public function actionDetail($id)
	{
		/** @var $ticket Ticket */
		if(!$ticket = $this->ticketRepository->find($id)) {
			$this->flashError('Tento ticket neexistuje.');
			$this->redirect('default');
		}
		if($ticket->public || (!($ticket->public) && $this->user->isLoggedIn() && $ticket->user->id === $this->user->id) || $this->user->isAllowed('ticket', 'assign')) {
			$this->template->ticket = $this->ticket = $ticket;
		} else {
			$this->flashError('Nemáte přístup k tomuto ticketu');
			$this->redirect('default');
		}
	}

	public function actionNew()
	{
		if(!$this->user->isLoggedIn()) {
			$this->flashError('Pro vytvoření ticketu se musíš přihlásit.');
			$this->redirect('default');
		}
	}

	public function actionMy()
	{
		if(!$this->user->isLoggedIn()) {
			$this->redirect('default');
		}
		$this->template->tickets = $this->ticketRepository->findByOwner($this->userRepository->find($this->user->id));
	}

	public function actionForMe()
	{
		if(!$this->user->isLoggedIn()) {
			$this->redirect('default');
		}
		$this->template->tickets = $this->ticketRepository->findByAssignedUser($this->userRepository->find($this->user->id));
	}

	public function actionUnassigned()
	{
		if(!$this->user->isLoggedIn() || !$this->user->isAllowed('ticket', 'assign')) {
			$this->redirect('default');
		}
		$this->template->tickets = $this->ticketRepository->findUnassigned();
	}

	protected function createComponentNewTicketForm()
	{
		$form = $this->createForm();
		$form->addSelect('game', 'Čeho se ticket týká', [NULL => 'Webové stránky'] + $this->gameRepository->findPairs());
		$form->addText('title', 'Titulek')
			->setOption('description', 'Popiš několika slovy obsah ticketu. Titulek "Pomoc!!!" nám nic neřekne.')
			->setRequired('Prosím zadej titulek');
		$form->addTextArea('text', 'Obsah')
			->setOption('description', 'Popiš podrobně, co nám chceš sdělit. Pokud nastala chyba, popiš, jak se chyba stala. Pokud máš screenshot, určitě pomůže taky.')
			->setAttribute('class', 'ckeditor');
		$form->addCheckbox('public', 'Ticket je veřejný');
		$form->addSubmit('NewTicket', 'Vytvořit ticket');
		$form->onSuccess[] = $this->newTicketFormSuccess;
		return $form;
	}

	public function newTicketFormSuccess(Form $form)
	{
		$v = $form->getValues();
		$ticket = new Ticket();
		$ticket->user = $this->userRepository->find($this->user->id);
		$ticket->assignedUser = NULL;
		$ticket->dateCreated = time();
		$ticket->game = $v->game ? $this->gameRepository->find($v->game) : NULL;
		$ticket->title = $v->title;
		$ticket->text = $v->text;
		$ticket->status = $ticket::STATUS_WAITING;
		$ticket->priority = 0;
		$ticket->public = $v->public;
		$this->ticketRepository->persist($ticket);
		$this->flashSuccess('Ticket byl vytvořen a nyní čeká na vyjádření admin teamu.');
		$this->redirect('detail', ['id' => $ticket->id]);
	}

	protected function createComponentAddResponseForm()
	{
		$form = $this->createForm();
		$form->addText('text', 'Odpověď')
			->setRequired('Prosím zadej odpověď.')
			->setAttribute('class', 'ckeditor');
		$form->addSubmit('addResponse', 'Přidat odpověď');
		$form->onSuccess[] = $this->addResponseFormSuccess;
		return $form;
	}

	public function addResponseFormSuccess(Form $form)
	{
		$v = $form->getValues();
		$this->ticketResponseRepository->addResponse($this->ticket, $this->userRepository->find($this->user->id), $v->text);
		$this->flashSuccess('Odpověď byla přidána');
		$this->refresh();
	}

	public function handleStatus($status)
	{
		$status = (int) $status;
		$previousStatus = $this->ticket->status;
		if($previousStatus === $status) {
			$this->refresh();
		}
		$oldStatus = $this->ticket->getStatusMessage();
		$this->ticket->status = $status;
		$this->ticketRepository->persist($this->ticket);
		$message = Html::el('i');
		$message->setHtml('Status změněn z "<b>' . $oldStatus . '</b>" na "<b>' . $this->ticket->getStatusMessage() . '</b>".');

		$this->ticketResponseRepository->addResponse($this->ticket, $this->userRepository->find($this->user->id), $message);
		$this->flashSuccess(strip_tags($message));
		$this->refresh();
	}

	public function handleAssign()
	{
		if(!$this->user->isAllowed('ticket', 'assign')) {
			$this->flashError('Nemůžeš si přiřazovat tickety!');
		}
		if(!$this->ticket->assignedUser) {
			$this->ticket->assignedUser = $this->userRepository->find($this->user->id);
			$this->ticketRepository->persist($this->ticket);
			$this->ticketResponseRepository->addResponse($this->ticket, $this->userRepository->find($this->user->id), "<b>Přiřadil jsem si tento ticket</b>.");
		}
		$this->refresh();
	}
}
