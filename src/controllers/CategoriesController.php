<?php

namespace App\Controllers;

use App\Common\Controller;
use App\Models\Categories;
use App\ResponseException;
use Exception;

class CategoriesController extends Controller
{

  /**
   * @param $body
   * @return Categories
   * @throws ResponseException
   */
  private function createCategory(array $body)
  {
    $category = new Categories();
    $category->name = trim($body['name']);
    $this->tryToSaveData($category, 'common.COULD_NOT_BE_CREATED');
    return $category;
  }

  /**
   * @param $category
   * @param $body
   * @return mixed
   * @throws ResponseException
   */
  private function updateCategory($category, $body)
  {
    $category->name = trim($body['name']);
    $this->tryToSaveData($category, 'common.COULD_NOT_BE_UPDATED');
    return $category;
  }

  /**
   * Get categories
   */
  public function all()
  {
    try {
      $this->initializeGet();
      $model = new Categories();
      $options = $this->buildOptions('name asc');
      $filters = $this->buildFilters($this->request->get('filter'));
      $categories = $this->findElements($model, $filters['conditions'], $filters['parameters'], 'id, name', $options['order_by'], $options['offset'], $options['limit'], true);
      $total = $this->calculateTotalElements($model, $filters['conditions'], $filters['parameters'], true);
      $data = $this->buildListingObject($categories, $options['rows'], $total);
      $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $data);
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      die($e);
    }
  }

  /** Create a new category
   * @throws Exception
   */
  public function create()
  {
    try {
      $this->initializePost();
      $rawBody = $this->request->getJsonRawBody(true);
      $this->checkForEmptyData($rawBody, ['name']);
      $category = $this->createCategory($rawBody);
//      $this->registerLog();
      $this->buildSuccessResponse(201, 'common.CREATED_SUCCESSFULLY', $category->toArray());
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      die($e);
    }
  }

  /** Get a category
   * @param $id
   */
  public function get($id)
  {
    try {
      $this->initializeGet();
      $model = new Categories();
      $category = $this->findElementById($model, $id);
      $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $category->toArray());
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      die($e);
    }
  }

  /** Update a category
   * @param $id
   * @throws Exception
   */
  public function update($id)
  {
    try {
      $this->initializePatch();
      $model = new Categories();
      $rawBody = $this->request->getJsonRawBody(true);
      $this->checkForEmptyData($rawBody, ['name']);
      $category = $this->updateCategory($this->findElementById($model, $id), $rawBody);
//      $this->registerLog();
      $this->buildSuccessResponse(200, 'common.UPDATED_SUCCESSFULLY', $category->toArray());
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      $this->buildErrorResponse(444, $e->getMessage());
    }
  }

  /** Delete a category
   * @param $id
   * @throws Exception
   */
  public function delete($id)
  {
    try {
      $this->initializeDelete();
      $model = new Categories();
      $this->tryToDeleteData($this->findElementById($model, $id));
//      $this->registerLog();
      $this->buildSuccessResponse(200, 'common.DELETED_SUCCESSFULLY');
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      die($e);
    }
  }
}