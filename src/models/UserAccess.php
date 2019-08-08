<?php

namespace App\Models;

use Phalcon\Mvc\Model;

/**
 * Class UserAccess
 * @property string email
 * @property string ip
 * @property string platform
 * @property string date
 * @package App\Models
 */
class UserAccess extends Model {

  public $id;
  public $email;
  public $ip;
  public $platform;
  public $date;

  /**
   * Initialize method for model.
   */
  public function initialize()
  {
    $this->setConnectionService('db');
  }

  /**
   * @return string
   */
  public function getSource()
  {
    return 'user_access';
  }

  /**
   * @return array
   */
  public function columnMap()
  {
    return [
      'id' => 'id',
      'email' => 'email',
      'ip' => 'ip',
      'platform' => 'platform',
      'date' => 'date',
    ];
  }
}