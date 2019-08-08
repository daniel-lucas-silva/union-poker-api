<?php

namespace App\Controllers;

use App\Common\Controller;
use App\Models\Images;
use App\ResponseException;
use Exception;

class ImagesController extends Controller
{

  /**
   * @param $body
   * @return Images
   * @throws ResponseException
   */
  private function createImage(array $body)
  {
    $image = new Images();
    $image->name = trim($body['name']);
    $this->tryToSaveData($image, 'common.COULD_NOT_BE_CREATED');
    return $image;
  }

  /**
   * @param $image
   * @param $body
   * @return mixed
   * @throws ResponseException
   */
  private function updateImage($image, $body)
  {
    $image->name = trim($body['name']);
    $this->tryToSaveData($image, 'common.COULD_NOT_BE_UPDATED');
    return $image;
  }

  /**
   * Get images
   */
  public function all()
  {
    try {
      $this->initializeGet();
      $model = new Images();
      $options = $this->buildOptions('name asc');
      $filters = $this->buildFilters($this->request->get('filter'));
      $images = $this->findElements($model, $filters['conditions'], $filters['parameters'], 'id, name', $options['order_by'], $options['offset'], $options['limit'], true);
      $total = $this->calculateTotalElements($model, $filters['conditions'], $filters['parameters'], true);
      $data = $this->buildListingObject($images, $options['rows'], $total);
      $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $data);
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      die($e);
    }
  }

  /** Create a new image
   * @throws Exception
   */
  public function create()
  {
    try {
      $this->initializePost();
      $rawBody = $this->request->getJsonRawBody(true);
      $this->checkForEmptyData($rawBody, ['name']);
      $image = $this->createImage($rawBody);
      $this->registerLog();
      $this->buildSuccessResponse(201, 'common.CREATED_SUCCESSFULLY', $image->toArray());
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      die($e);
    }
  }

  /** Get a image
   * @param $id
   */
  public function get($id)
  {
    try {
      $this->initializeGet();
      $model = new Images();
      $image = $this->findElementById($model, $id);
      $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $image->toArray());
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      die($e);
    }
  }

  /** Update a image
   * @param $id
   * @throws Exception
   */
  public function update($id)
  {
    try {
      $this->initializePatch();
      $model = new Images();
      $rawBody = $this->request->getJsonRawBody(true);
      $this->checkForEmptyData($rawBody, ['name']);
      $image = $this->updateImage($this->findElementById($model, $id), $rawBody);
      $this->registerLog();
      $this->buildSuccessResponse(200, 'common.UPDATED_SUCCESSFULLY', $image->toArray());
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      $this->buildErrorResponse(444, $e->getMessage());
    }
  }

  /** Delete a image
   * @param $id
   * @throws Exception
   */
  public function delete($id)
  {
    try {
      $this->initializeDelete();
      $model = new Images();
      $this->tryToDeleteData($this->findElementById($model, $id));
      $this->registerLog();
      $this->buildSuccessResponse(200, 'common.DELETED_SUCCESSFULLY');
    }
    catch (ResponseException $e) {
      $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
    } catch (Exception $e) {
      die($e);
    }
  }
}