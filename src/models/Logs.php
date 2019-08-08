<?php
namespace App\Models;

use Phalcon\Mvc\Model;

/**
 * Class Logs
 * @package App\Models
 * @property integer $id
 * @property string $email
 * @property string $route
 * @property string $date
 */
class Logs extends Model
{
  public $id;
  public $email;
  public $route;
  public $date;

  /**
   * Initialize method for model.
   */
  public function initialize()
  {
    $this->setConnectionService('db_log'); // Connection service for log database
  }

  /**
   * @return string
   */
  public function getSource()
  {
    return 'logs';
  }

  /**
   * @return array
   */
  public function columnMap()
  {
    return array(
      'id' => 'id',
      'email' => 'email',
      'route' => 'route',
      'date' => 'date',
    );
  }
}