<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\AdminModule;

class OverviewPresenter extends BaseSpecificAdminPresenter
{
	protected function checkPermissions()
	{
		return TRUE;
	}
}
 