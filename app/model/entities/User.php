<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\Model;

/**
 * @property-read int $id
 * @property string $nick
 * @property string $password
 * @property string $email
 * @property string $salt
 * @property string $role
 * @property bool $verified = FALSE
 */
class User extends BaseEntity {

}
