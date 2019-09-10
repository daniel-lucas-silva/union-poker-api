<?php

namespace App\Models;

use App\Common\CacheableModel;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;
use Phalcon\Validation\Validator\Uniqueness as UniquenessValidator;

/**
 * Class Players
 * @property string id
 * @property string agent_id
 * @property string email
 * @property string username
 * @property string password
 * @property string name
 * @property string avatar
 * @package App\Models
 */
class Players extends CacheableModel {

  public $id;
  public $agent_id;
  public $email;
  public $username;
  public $password;
  public $name;
  public $avatar;
  public $created_at;
  public $updated_at;

  /**
   * Initialize method for model.
   */
  public function initialize()
  {
    self::$key = 'players';
    $this->setConnectionService('db');

    $this->hasMany( 'id', 'Transactions', 'player_id',
      ['alias' => 'transactions']
    );

      $this->belongsTo( 'agent_id', 'Users', 'id',
          ['alias' => 'agent']
      );
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
    return 'players';
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
      'agent_id' => 'agent_id',
      'email' => 'email',
      'username' => 'username',
      'password' => 'password',
      'name' => 'name',
      'avatar' => 'avatar',
      'created_at' => 'created_at',
      'updated_at' => 'updated_at',
    ];
  }
}