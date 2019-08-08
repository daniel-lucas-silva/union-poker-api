<?php

namespace App\Controllers;

use App\Common\Controller;

class IndexController extends Controller {
  public function index() {
    $this->buildErrorResponse(200, 'common');
  }
}