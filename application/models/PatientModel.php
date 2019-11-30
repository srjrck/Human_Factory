<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Author: https://www.roytuts.com
 */
class PatientModel extends CI_model {

    function __construct() {
        parent::__construct();
    }

    function create_patient($resouceData, $identifierData, $nameData, $telecomData) {
        if ($resouceData) {
            $found = false;
            $identifierDataForSave = [];
            $nameDataForSave = [];
            $telecomDataForSave = [];
            if (isset($identifierData) && count($identifierData) > 0) {
                $idArr = [];
                $this->db->select("id");
                $this->db->from("resource");
                $this->db->where('type', 'Patient');
                $this->db->where('is_removed', 'false');
                $query = $this->db->get();
                $result = $query->result_array();
                if (isset($result) && count($result) > 0) {
                    for($i=0;$i<count($result);$i++){
                        array_push($idArr,$result[$i]['id']);
                    }
                }

                for ($i = 0; $i < count($identifierData); $i++) {
                    $this->db->select("*");
                    $this->db->from("identifier");
                    $this->db->where('value', $identifierData[$i]['value']);
                    $this->db->where('type', $identifierData[$i]['type']);
                    if(count($idArr)>0){
                    $this->db->where_in('resource_id', $idArr);
                    }
                    $this->db->where('is_removed', 'false');
                    $query = $this->db->get();
                    $result = $query->result_array();
                    if (count($result) > 0) {
                        $found = true;
                    }
                }
            }
            if (!$found) {
                $this->db->insert('resource', $resouceData);
                $insert_id = $this->db->insert_id();
                if (isset($identifierData) && count($identifierData) > 0) {
                    for ($i = 0; $i < count($identifierData); $i++) {
                        $this->db->insert('identifier', array('resource_id' => $insert_id, 'type' => $identifierData[$i]['type'], 'value' => $identifierData[$i]['value']));
                    }
                }
                if (isset($nameData) && count($nameData) > 0) {
                    for ($i = 0; $i < count($nameData); $i++) {
                        $this->db->insert('name', array('resource_id' => $insert_id, 'family' => $nameData[$i]['family'], 'given' => $nameData[$i]['given']));
                    }
                }
                if (isset($telecomData) && count($telecomData) > 0) {
                    for ($i = 0; $i < count($telecomData); $i++) {
                        $this->db->insert('telecom', array('resource_id' => $insert_id, 'system' => $telecomData[$i]['system'], 'value' => $telecomData[$i]['value']));
                    }
                }
                http_response_code(201);
                $full_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                header("Location: $full_url/$insert_id");
                $response['resourcetype'] = "OperationOutcome";
                $response['issue']['severity'] = "success";
                $response['issue']['code'] = "created";
                $response['issue']['details']['text'] = "Patient created successfully";
            } else {
                http_response_code(409);
                $response['resourcetype'] = "OperationOutcome";
                $response['issue'][0]['severity'] = "error";
                $response['issue'][0]['code'] = "conflict";
                $response['issue'][0]['details']['text'] = "Patient with identifiers already exists";
            }
        } else {
            http_response_code(400);
            $response['resourcetype'] = "OperationOutcome";
            $response['issue']['severity'] = "error";
            $response['issue']['code'] = "resourcetype";
            $response['issue']['details']['text'] = "Sorry! The body that you passes in, is not valid according to practitioner structure.";
        }
        return $response;
    }

}

?>
