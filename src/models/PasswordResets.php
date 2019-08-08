<?php
namespace App\Models;

use Phalcon\Mvc\Model;

/**
 * Class PasswordResets
 * @package App\Models
 */
class PasswordResets extends Model
{
  public $email;
  public $token;
  public $created_at;

  /**
   * Initialize method for model.
   */
  public function initialize()
  {
    $this->setConnectionService('db'); // Connection service for log database
  }

  /**
   * @return string
   */
  public function getSource()
  {
    return 'password_resets';
  }

  /**
   * @return array
   */
  public function columnMap()
  {
    return array(
      'email' => 'email',
      'token' => 'token',
      'created_at' => 'created_at',
    );
  }
}