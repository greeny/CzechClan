<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\Model;

/**
 * @property-read int $id
 * @property User $author m:hasOne
 * @property Category|NULL $category m:hasOne
 * @property string $title
 * @property string $content
 * @property string $slug
 * @property int $published
 */
class Article extends BaseEntity
{

}
 