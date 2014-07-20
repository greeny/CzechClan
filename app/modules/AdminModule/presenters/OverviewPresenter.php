<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\AdminModule;

class OverviewPresenter extends BaseSpecificAdminPresenter
{
	protected function checkPermissions()
	{
		return TRUE;
	}
}
