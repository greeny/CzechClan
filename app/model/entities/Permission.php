<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\Model;

/**
 * @property-read int $id
 * @property Role $role m:hasOne
 * @property Game $game m:hasOne
 * @property string $resource
 * @property string $privilege
 * @property bool $allow
 */
class Permission extends BaseEntity
{

}
 