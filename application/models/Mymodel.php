<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Author: https://www.roytuts.com
 */
class Mymodel extends CI_model {

    function __construct() {
        parent::__construct();
    }
    
    function get_data($id) {
        http_response_code(200);
        $response['resourcetype'] = "OperationOutcome";
        $response['issue']['severity'] = "success";
        $response['issue']['code'] = "";
        $response['issue']['details']['text'] = "all data removed successfully: $id";
    }
    
}