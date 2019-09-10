<?php

namespace App\Controllers;

use App\Common\Controller;
use App\Models\Banks;
use App\ResponseException;
use Phalcon\Db;
use Exception;

/**
 * Class BanksController
 * @package App\Controllers
 */
class BanksController extends Controller
{

    /**
     * @param $body
     * @return Banks
     * @throws ResponseException
     */
    private function createBank(array $body)
    {
        $bank = new Banks();
        $bank->name = trim($body['name']);
        $this->tryToSaveData($bank, 'common.COULD_NOT_BE_CREATED');
        return $bank;
    }

    /**
     * @param $bank
     * @param $body
     * @return mixed
     * @throws ResponseException
     */
    private function updateBank($bank, $body)
    {
        $bank->name = trim($body['name']);
        $this->tryToSaveData($bank, 'common.COULD_NOT_BE_UPDATED');
        return $bank;
    }

    /**
     * Get banks
     */
    public function all()
    {
        try {
            $this->initializeGet();
            $options = $this->buildOptions('name asc');
            $filters = $this->buildFilters($this->request->get('filter'));
            $term = str_replace(' ', '**', trim($this->request->get('search')));

            $where = strlen($filters['conditions']) > 0 ? "WHERE {$filters['conditions']}" : "";
            $sql = "SELECT
                    banks.id AS id,
                    banks.name AS name,
                    banks.created_at AS created_at
                  FROM banks
                  LEFT JOIN transactions
                  ON transactions.bank_id = banks.id
                  {$where}
                  ORDER BY {$options['order_by']}
                  LIMIT {$options['limit']} 
                  OFFSET {$options['offset']}";

            if (strlen($term)) {
                // Do fulltext search
                $sql = "SELECT
                    banks.id AS id,
                    banks.name AS name,
                    banks.created_at AS created_at,
                    MATCH (name, ag, cc, manager_name, manager_email) AGAINST ('*{$term}*') AS relevance
                  FROM banks
                  LEFT JOIN transactions
                  ON transactions.bank_id = banks.id
                  WHERE MATCH (name, ag, cc, manager_name, manager_email) AGAINST ('*{$term}*' IN BOOLEAN MODE) {$filters['conditions']}
                  ORDER BY relevance DESC, {$options['order_by']}
                  LIMIT {$options['limit']} 
                  OFFSET {$options['offset']}";
            }

            $query = $this->db->query($sql);
            $query->setFetchMode(Db::FETCH_ASSOC);
            $banks = $query->fetchAll();
            $total = $query->numRows();

            if (!$total) {
                throw new ResponseException(404, 'common.NOT_RECORDS');
            }

            $data = $this->buildListingObject($banks, $options['rows'], $total);
            $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $data);
        } catch (ResponseException $e) {
            $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
        } catch (Exception $e) {
            $this->buildErrorResponse(500, "Internal Error");
        }
    }

    /** Create a new bank
     * @throws Exception
     */
    public function create()
    {
        try {
            $this->initializePost();
            $rawBody = $this->request->getJsonRawBody(true);
            $this->checkForEmptyData($rawBody, ['name']);
            $bank = $this->createBank($rawBody);
            $this->registerLog();
            $this->buildSuccessResponse(201, 'common.CREATED_SUCCESSFULLY', $bank->toArray());
        } catch (ResponseException $e) {
            $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
        } catch (Exception $e) {
            die($e);
        }
    }

    /** Get a bank
     * @param $id
     */
    public function get($id)
    {
        try {
            $this->initializeGet();
            $model = new Banks();
            $bank = $this->findElementById($model, $id);
            $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $bank->toArray());
        } catch (ResponseException $e) {
            $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
        } catch (Exception $e) {
            die($e);
        }
    }

    /** Update a bank
     * @param $id
     * @throws Exception
     */
    public function update($id)
    {
        try {
            $this->initializePatch();
            $model = new Banks();
            $rawBody = $this->request->getJsonRawBody(true);
            $this->checkForEmptyData($rawBody, ['name']);
            $bank = $this->updateBank($this->findElementById($model, $id), $rawBody);
            $this->registerLog();
            $this->buildSuccessResponse(200, 'common.UPDATED_SUCCESSFULLY', $bank->toArray());
        } catch (ResponseException $e) {
            $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
        } catch (Exception $e) {
            $this->buildErrorResponse(444, $e->getMessage());
        }
    }

    /** Delete a bank
     * @param $id
     * @throws Exception
     */
    public function delete($id)
    {
        try {
            $this->initializeDelete();
            $model = new Banks();
            $this->tryToDeleteData($this->findElementById($model, $id));
            $this->registerLog();
            $this->buildSuccessResponse(200, 'common.DELETED_SUCCESSFULLY');
        } catch (ResponseException $e) {
            $this->buildErrorResponse($e->getCode(), $e->getMessage(), $e->getData());
        } catch (Exception $e) {
            die($e);
        }
    }
}