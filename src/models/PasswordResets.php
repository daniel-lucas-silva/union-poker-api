<?php
namespace App\Models;

use Phalcon\Mvc\Model;

/**
 * Class PasswordResets
 * @package App\Models
 */
class PasswordResets extends Model
{
  public $id;
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
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Users[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }
    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Users
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

  /**
   * @return array
   */
  public function columnMap()
  {
    return array(
      'id' => 'id',
      'email' => 'email',
      'token' => 'token',
      'created_at' => 'created_at',
    );
  }
}