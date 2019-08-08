<?php
namespace App\Models;

use App\Common\CacheableModel;

/**
 * Class Categories
 * @package App\Models
 */
class Categories extends CacheableModel
{
  public $id;
  public $parent_id;
  public $children_count;
  public $name;
  public $slug;
  public $created_at;
  public $updated_at;

  /**
   * Initialize method for model.
   */
  public function initialize()
  {
    self::$key = "categories";
    $this->setConnectionService('db'); // Connection service for log database
  }

  /**
   * @return string
   */
  public function getSource()
  {
    return 'categories';
  }

  /**
   * @return array
   */
  public function columnMap()
  {
    return array(
      'id'              => 'id',
      'parent_id'       => 'parent_id',
      'children_count'  => 'children_count',
      'name'            => 'name',
      'slug'            => 'slug',
      'created_at'      => 'created_at',
      'updated_at'      => 'updated_at',
    );
  }
}