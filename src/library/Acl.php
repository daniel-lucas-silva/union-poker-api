<?php

namespace App;

use App\Common\Controller;
use Exception;
use Phalcon\Mvc\Micro\MiddlewareInterface;
use Phalcon\Mvc\Micro;

/**
 * Class Acl
 * @package App
 */
class Acl extends Controller implements MiddlewareInterface
{
    /**
     * @param Micro $app
     * @return int|void
     */
    public function call(Micro $app)
    {

        /** Initialize
         * Gets users ACL
         * @global array $arrResources
         * @global \Phalcon\Acl $acl
         */
        include __DIR__ . '/../acl.php';

        $arrHandler = $app->getActiveHandler();
        //get the controller for this handler
        $array = (array) $arrHandler[0];
        $nameController = implode('', $array);

        $controller = str_replace('App\\Controllers\\', '', $nameController);
        $controller = str_replace('Controller', '', $controller);
        // get function
        $function = $arrHandler[1];

        // check if exist a controllers and functions in ACL Guest, so return allow
        if (array_key_exists($controller, $arrResources['guest']) && in_array($function, $arrResources['guest'][$controller])) {
            $allowed = 1;
            return $allowed;
        }

        // gets user token
        $token = $this->getToken();
        // Verifies Token exists and is not empty
        if (empty($token) || $token == '') {
            $this->buildErrorResponse(400, 'common.EMPTY_TOKEN_OR_NOT_RECEIVED');
        } else {
            // Verifies Token
            try {
                $token_decoded = $this->decodeToken($token);
                // Verifies User role Access
                $allowed_access = $acl->isAllowed($token_decoded->role, $controller, $arrHandler[1]);
                return (!$allowed_access) ? $this->buildErrorResponse(403, 'common.YOUR_USER_ROLE_DOES_NOT_HAVE_THIS_FEATURE') : $allowed_access;
            } catch (Exception $e) {
                // BAD TOKEN
                $this->buildErrorResponse(401, 'common.BAD_TOKEN_GET_A_NEW_ONE');
            }
        }
    }
}
