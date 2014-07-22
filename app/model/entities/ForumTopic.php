<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

/**
 * @property-read int $id
 * @property int $left
 * @property int $right
 * @property Game $game m:hasOne
 * @property Role[] $allowedRoles m:hasMany
 * @property User[] $allowedUsers m:hasMany
 * @property string $title
 * @property string $subtitle
 * @property bool $public=TRUE
 * @property-read NULL|ForumPost $lastPost m:hasOne(last_post_id)
 */
class ForumTopic extends BaseEntity
{
}
