<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

/**
 * @property-read int $id
 * @property Game $game m:hasOne
 * @property string $name
 * @property string $slug
 * @property Article[] $articles m:belongsToMany
 */
class Category extends BaseEntity
{

}
