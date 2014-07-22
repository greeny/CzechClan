<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

/**
 * @property-read int $id
 * @property ForumTopic $topic m:hasOne(topic_id)
 * @property string $title
 * @property User $user m:hasOne
 * @property int $dateCreated (date_created)
 * @property bool $locked=FALSE
 * @property bool $pinned=FALSE
 * @property-read ForumPost $lastPost m:hasOne(last_post_id)
 */
class ForumThread extends BaseEntity
{
}
