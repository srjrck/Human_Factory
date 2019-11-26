<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Author: https://www.roytuts.com
 */
class FakeresourceModel extends CI_model {

    function __construct() {
        parent::__construct();
    }

    function fakeresource($method) {
        http_response_code(404);
        $response['status'] = "error";
        $response['msg'] = "Unknown resource $method";
        return $response;
    }

}
