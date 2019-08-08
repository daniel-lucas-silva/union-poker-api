<?php
namespace App\Models;

use App\Common\CacheableModel;

/**
 * Class Images
 * @package App\Models
 */
class Images extends CacheableModel
{
  public $id;
  public $path;
  public $name;
  public $imageable_id;
  public $imageable_type;
  public $order;
  public $updated_at;
  public $created_at;

  /**
   * Initialize method for model.
   */
  public function initialize()
  {
    self::$key = "images";
    $this->setConnectionService('db'); // Connection service for log database
  }

  /**
   * @return string
   */
  public function getSource()
  {
    return 'images';
  }

  /**
   * @return array
   */
  public function columnMap()
  {
    return array(
      'id' => 'id',
      'path' => 'path',
      'name' => 'name',
      'imageable_id' => 'imageable_id',
      'imageable_type' => 'imageable_type',
      'order' => 'order',
      'updated_at' => 'updated_at',
      'created_at' => 'created_at',
    );
  }
}