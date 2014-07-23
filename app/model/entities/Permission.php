<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

/**
 * @property-read int $id
 * @property Role $role m:hasOne
 * @property Game|NULL $game m:hasOne
 * @property string $resource
 */
class Permission extends BaseEntity
{
	public static $generalResources = array(
		'user' => 'Administrace uživatelů',
		'role' => 'Administrace rolí',
		'game' => 'Administrace her',
	);

	public static $specificResources = array(
		'article' => 'Administrace článků',
		'information' => 'Administrace informací',
		'forum' => 'Administrace fóra',
	);
}
