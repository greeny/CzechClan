<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

/**
 * @property-read int $id
 * @property ForumThread $thread m:hasOne(thread_id)
 * @property User $user m:hasOne
 * @property ForumPostContent $content m:belongsToOne(post_id)
 * @property string $title
 * @property int $order
 * @property int $datePosted (date_posted)
 * @property int $timesEdited = 0 (times_edited)
 * @property int $dateLastEdit = 0 (date_last_edit)
 */
class ForumPost extends BaseEntity
{

}
