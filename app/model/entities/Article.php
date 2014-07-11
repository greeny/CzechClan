<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\Model;

/**
 * @property-read int $id
 * @property User $author (author_id)
 * @property Category|NULL $category
 * @property string $title
 * @property string $slug
 * @property int $published
 */
class Article extends BaseEntity
{

}
 