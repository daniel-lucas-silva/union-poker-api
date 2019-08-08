<?php
namespace App\Models;

use App\Common\CacheableModel;

/**
 * Class Searches
 * @package App\Models
 */
class Searches extends CacheableModel
{
  public $id;
  public $user_id;
  public $query;
  public $created_at;

  /**
   * Initialize method for model.
   */
  public function initialize()
  {
    self::$key = "searches";
    $this->setConnectionService('db'); // Connection service for log database
  }

  /**
   * @return string
   */
  public function getSource()
  {
    return 'searches';
  }

  /**
   * @return array
   */
  public function columnMap()
  {
    return array(
      'id' => 'id',
      'user_id' => 'user_id',
      'query' => 'query',
      'created_at' => 'created_at',
    );
  }
}