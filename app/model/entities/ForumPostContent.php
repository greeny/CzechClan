<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

/**
 * @property ForumPost $post m:hasOne(post_id)
 * @property string $text
 */
class ForumPostContent extends BaseEntity
{

}
