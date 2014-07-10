<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\Model;

/**
 * @property-read int $id
 * @property Role|NULL $parent m:hasOne
 * @property string $name
 * @property bool $displayLabel (display_label)
 * @property string $labelClass (label_class)
 * @property string $labelText (label_text)
 */
class Role extends BaseEntity
{

}
 