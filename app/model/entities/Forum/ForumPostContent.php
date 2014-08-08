<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

/**
 * @property int $id
 * @property ForumPost|NULL $post m:hasOne(post_id)
 * @property string $text
 */
class ForumPostContent extends BaseEntity
{

}
