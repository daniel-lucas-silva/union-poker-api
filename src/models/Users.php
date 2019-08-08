<?php

namespace App\Models;

use App\Common\CacheableModel;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;
use Phalcon\Validation\Validator\Uniqueness as UniquenessValidator;

/**
 * Class User
 * @property string id
 * @property string email
 * @property string username
 * @property string password
 * @property string name
 * @property string avatar
 * @property string role
 * @property string active
 * @property string login_attempts
 * @property string last_login
 * @property string block_expires
 * @package App\Models
 */
class Users extends CacheableModel {

  public $id;
  public $email;
  public $username;
  public $password;
  public $name;
  public $avatar;
  public $role;
  public $active;
  public $login_attempts;
  public $last_login;
  public $block_expires;
  public $created_at;
  public $updated_at;

  /**
   * Initialize method for model.
   */
  public function initialize()
  {
    self::$key = "users";
    $this->setConnectionService('db');

//    $this->hasOne(
//      'id',
//      Listings::class,
//      'user_id',
//      [
//        'reusable' => true, // cache related data
//        'alias'    => 'listing',
//      ]
//    );

  }

  /**
   * @return bool
   */
  public function validation()
  {
    $validator = new Validation();
    $validator->add(
      'username',
      new UniquenessValidator(['message' => 'validation.email.EXISTS'])
    );
    $validator->add(
      'email',
      new EmailValidator(['message' => 'validation.email.INVALID'])
    );
    $validator->add(
      'email',
      new UniquenessValidator(['message' => 'validation.email.EXISTS'])
    );
    return $this->validate($validator);
  }

  /**
   * Returns table name mapped in the model.
   *
   * @return string
   */
  public function getSource()
  {
    return 'users';
  }

  /**
   * Independent Column Mapping.
   * Keys are the real names in the table and the values their names in the application
   *
   * @return array
   */
  public function columnMap()
  {
    return [
      'id' => 'id',
      'email' => 'email',
      'username' => 'username',
      'password' => 'password',
      'name' => 'name',
      'avatar' => 'avatar',
      'role' => 'role',
      'active' => 'active',
      'login_attempts' => 'login_attempts',
      'last_login' => 'last_login',
      'block_expires' => 'block_expires',
      'created_at' => 'created_at',
      'updated_at' => 'updated_at',
    ];
  }
}