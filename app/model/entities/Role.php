<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\Model;

/**
 * @property-read int $id
 * @property Role|NULL $parent m:hasOne(parent_id)
 * @property string $name
 * @property bool $displayLabel (display_label)
 * @property string $labelClass (label_class)
 * @property string $labelText (label_text)
 * @property Permission[] $permissions m:belongsToMany
 * @property User[] $users m:hasMany(:user_role)
 */
class Role extends BaseEntity
{

}
 