<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\AdminModule;

use CzechClan\Controls\Form;
use CzechClan\Model\Permission;
use CzechClan\Model\PermissionRepository;
use CzechClan\Model\Role;
use CzechClan\Model\RoleRepository;
use CzechClan\Model\UserRepository;
use Nette\Utils\Paginator;

class PermissionPresenter extends BaseGeneralAdminPresenter
{
	/** @var RoleRepository @inject */
	public $roleRepository;

	/** @var PermissionRepository @inject */
	public $permissionRepository;

	/** @var Role */
	protected $role;

	public function actionDefault($page = 1)
	{
		$paginator = new Paginator();
		$paginator->itemCount = $this->roleRepository->countAll();
		$paginator->itemsPerPage = 20;
		$paginator->page = $page;
		if($paginator->page !== $page) {
			$this->redirect('this', array('page' => $paginator->page));
		}
		$this->template->paginator = $paginator;
		$this->template->roles = $this->roleRepository->findOrderedByPage($paginator, '[name] ASC');
	}

	public function actionEdit($id)
	{
		$this->template->role = $this->role = $this->roleRepository->find($id);
	}

	protected function createRoleForm()
	{
		$form = $this->createForm();
		$form->addText('name', 'Jméno')
			->setRequired('Prosím zadej jméno.');
		$form->addCheckbox('displayLabel', 'Zobrazit odznak u jména');
		$form->addText('labelClass', 'CSS třída odznaku')
			->addCondition($form::FILLED)
			->addRule($form::FILLED, 'Prosím zadej CSS třídu odznaku');
		$form->addText('labelText', 'Text odznaku')
			->addCondition($form::FILLED)
			->addRule($form::FILLED, 'Prosím zadej text odznaku');
		return $form;
	}

	protected function createComponentAddRoleForm()
	{
		$form = $this->createRoleForm();
		$form->addSubmit('addRole', 'Přidat roli');
		$form->onSuccess[] = $this->AddRoleFormSuccess;
		return $form;
	}

	public function AddRoleFormSuccess(Form $form)
	{
		$v = $form->getValues();
		$this->roleRepository->persist($role = Role::from($v));
		$this->flashSuccess('Role byla vytvořena.');
		$this->redirect('edit', array('id' => $role->id));
	}

	protected function createComponentEditRoleForm()
	{
		$form = $this->createRoleForm();
		$form->setDefaults($this->role->getData());
		$form->addSubmit('editRole', 'Upravit roli');
		$form->onSuccess[] = $this->editRoleFormSuccess;
		return $form;
	}

	public function editRoleFormSuccess(Form $form)
	{
		$v = $form->getValues();
		$this->role->update($v);
		$this->roleRepository->persist($this->role);
		$this->flashSuccess('Role byla upravena.');
		$this->refresh();
	}

	protected function createComponentAddGeneralPermissionToRoleForm()
	{
		$form = $this->createForm();
		$blacklist = array();
		foreach($this->role->permissions as $permission) {
			$blacklist[] = $permission->resource;
		}
		$form->addSelect('resource', 'Oprávnění', $this->permissionRepository->getGeneralResources($blacklist))
			->setPrompt(' - Vyber oprávnění - ')
			->setRequired('Prosím zadej oprávnění.');
		$form->addSubmit('addGeneralPermissionToRole', 'Přidat oprávnění roli');
		$form->onSuccess[] = $this->addGeneralPermissionToRoleFormSuccess;
		return $form;
	}

	public function addGeneralPermissionToRoleFormSuccess(Form $form)
	{
		$v = $form->getValues();
		$permission = Permission::from($v);
		$permission->game = NULL;
		$permission->role = $this->role;
		try {
			$this->permissionRepository->persist($permission);
			$this->flashSuccess('Oprávnění bylo přidáno.');
		} catch(\DibiDriverException $e) {} // ignore duplicate keys here
		$this->refresh();
	}

	protected function createComponentAddSpecificPermissionToRoleForm()
	{
		$form = $this->createForm();
		$form->addSelect('game', 'Hra', $this->gameRepository->findPairs())
			->setPrompt(' - Vyber hru - ')
			->setRequired('Prosím zadej hru.');
		$form->addSelect('resource', 'Oprávnění', $this->permissionRepository->getSpecificResources())
			->setPrompt(' - Vyber oprávnění - ')
			->setRequired('Prosím zadej oprávnění.');
		$form->addSubmit('addSpecificPermissionToRole', 'Přidat oprávnění roli');
		$form->onSuccess[] = $this->addSpecificPermissionToRoleFormSuccess;
		return $form;
	}

	public function addSpecificPermissionToRoleFormSuccess(Form $form)
	{
		$v = $form->getValues();
		$permission = new Permission();
		$permission->resource = $v->resource;
		$permission->game = $this->gameRepository->find($v->game);
		$permission->role = $this->role;
		try {
			$this->permissionRepository->persist($permission);
			$this->flashSuccess('Oprávnění bylo přidáno.');
		} catch(\DibiDriverException $e) {} // ignore duplicate keys here
		$this->refresh();
	}

	protected function checkPermissions()
	{
		return $this->isAllowed('role');
	}

	public function handleDelete($id)
	{
		$role = $this->roleRepository->find($id);
		if($role) {
			$name = $role->name;
			$this->roleRepository->delete($role);
			$this->flashSuccess("Role '$name' byla smazána.");
		}
		$this->refresh();
	}

	public function handleRemovePermissionFromRole($permissionId)
	{
		$permission = $this->permissionRepository->find($permissionId);
		if($permission) {
			$this->permissionRepository->delete($permission);
			$this->flashSuccess('Oprávnění bylo odstraněno.');
		}
		$this->refresh();
	}
}
 