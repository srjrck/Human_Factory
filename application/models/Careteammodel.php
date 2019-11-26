<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Author: https://www.roytuts.com
 */
class CareteamModel extends CI_model {

    function __construct() {
        parent::__construct();
    }
    
    function get_careteam($id) {
        $response = [];
        $this->db->select("id,type");
        $this->db->from("resource");
        $this->db->where('id', $id);
        $this->db->where('type', 'CareTeam');
        $this->db->where('is_removed', 'false');
        $query = $this->db->get();
        $result = $query->result_array();
        if (count($result) > 0) {
            $identifier_data = [];
            $name_data = [];
            $telecom_data = [];
            $participant_data = [];
            $identifier_query = $this->db->get_where('identifier', array('resource_id' => $result[0]['id'], 'is_removed' => 'false'));
            $identifier_result = $identifier_query->result();
            if (isset($identifier_result) && count($identifier_result)>0) {
                for ($j = 0; $j < count($identifier_result); $j++) {
                    array_push($identifier_data, array('type' => $identifier_result[$j]->type, 'value' => $identifier_result[$j]->value));
                }
            }
            $name_query = $this->db->get_where('name', array('resource_id' => $result[0]['id'], 'is_removed' => 'false'));
            $name_result = $name_query->result();
            if (isset($name_result) && count($name_result)>0) {
                    $name_data = $name_result[0]->family;
            }
            $telecom_query = $this->db->get_where('telecom', array('resource_id' => $result[0]['id'], 'is_removed' => 'false'));
            $telecom_result = $telecom_query->result();
            if (isset($telecom_result) && count($telecom_result)>0) {
                for ($j = 0; $j < count($telecom_result); $j++) {
                    array_push($telecom_data, array('system' => $telecom_result[$j]->system, 'value' => $telecom_result[$j]->value));
                }
            }
            $participant_query = $this->db->get_where('participant', array('resource_id' => $result[0]['id'], 'is_removed' => 'false'));
            $participant_result = $participant_query->result();
            if (isset($participant_result) && count($participant_result)>0) {
                for ($j = 0; $j < count($participant_result); $j++) {
                    array_push($participant_data, json_decode($participant_result[$j]->data));
                }
            }
            $data['id'] = $result[0]['id'];
            $data['type'] = $result[0]['type'];
            $data['identifier'] = $identifier_data;
            $data['name'] = $name_data;
            $data['telecom'] = $telecom_data;
            if(count($participant_data)>0){
                $data['participant'] = $participant_data;
            }
            http_response_code(200);
            $response['status'] = "success";
            $response['msg'] = "Data retrieved successfully";
            $response['data'] = $data;
        } else {
            http_response_code(404);
            $response['resourcetype'] = "OperationOutcome";
            $response['issue']['severity'] = "error";
            $response['issue']['code'] = "notfound";
            $response['issue']['details']['text'] = "No such care team with id: $id";
        }
        return $response;
    }
    
    function check_careteam($id) {
        $this->db->select("id,type");
        $this->db->from("resource");
        $this->db->where('id', $id);
        $this->db->where('type', 'CareTeam');
        $this->db->where('is_removed', 'false');
        $query = $this->db->get();
        $result = $query->result_array();
        if (isset($result) && count($result) > 0) {
            http_response_code(200);
        } else {
            http_response_code(404);
        }
    }


    function create_careteam($resouceData, $identifierData, $nameData, $telecomData,$participantData) {
        if ($resouceData) {
            $found = false;
            $identifierDataForSave = [];
            $nameDataForSave = [];
            $telecomDataForSave = [];
            if (isset($identifierData) && count($identifierData) > 0) {
                $idArr = [];
                $this->db->select("id");
                $this->db->from("resource");
                $this->db->where('type', 'CareTeam');
                $this->db->where('is_removed', 'false');
                $query = $this->db->get();
                $result = $query->result_array();
                if (count($result) > 0) {
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
                if (isset($nameData)) {
                        $this->db->insert('name', array('resource_id' => $insert_id, 'family' => $nameData));
                }
                if (isset($telecomData) && count($telecomData) > 0) {
                    for ($i = 0; $i < count($telecomData); $i++) {
                        $this->db->insert('telecom', array('resource_id' => $insert_id, 'system' => $telecomData[$i]['system'], 'value' => $telecomData[$i]['value']));
                    }
                }
                if(isset($participantData) && count($participantData)>0){
                    for ($i = 0; $i < count($participantData); $i++) {
                        $this->db->insert('participant', array('resource_id' => $insert_id, 'data' => $participantData[$i]));
                    }
                }
                http_response_code(201);
                $full_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                header("Location: $full_url/$insert_id");
                $response['resourcetype'] = "OperationOutcome";
                $response['issue']['severity'] = "success";
                $response['issue']['code'] = "created";
                $response['issue']['details']['text'] = "Care team created successfully";
            } else {
                http_response_code(409);
                $response['resourcetype'] = "OperationOutcome";
                $response['issue']['severity'] = "error";
                $response['issue']['code'] = "conflict";
                $response['issue']['details']['text'] = "Care team with identifiers already exists";
            }
        } else {
            http_response_code(400);
            $response['resourcetype'] = "OperationOutcome";
            $response['issue']['severity'] = "error";
            $response['issue']['code'] = "resourcetype";
            $response['issue']['details']['text'] = "Sorry! The body that you passes in, is not valid according to care team structure.";
        }
        return $response;
    }

    function update_careteam($resource_id,$resouceData, $identifierData, $nameData, $telecomData,$participantData) {
        if ($resource_id) {
            $this->db->select("*");
            $this->db->from("resource");
            $this->db->where('id', $resource_id);
            $this->db->where('type', 'CareTeam');
            $this->db->where('is_removed', 'false');
            $query = $this->db->get();
            $result = $query->result_array();
            if (isset($result) && count($result) > 0) {
                //update resource table
                $this->db->set('json', $resouceData['json']);
                $this->db->where('type', 'CareTeam');
                $this->db->where('is_removed', 'false');
                $this->db->where('id', $resource_id);
                $this->db->update('resource');
                if ($this->db->affected_rows() > 0) {
                    //update identifier table
                    if (isset($identifierData) && count($identifierData) > 0) {
                        for ($i = 0; $i < count($identifierData); $i++) {
                            $this->db->set('value', $identifierData[$i]['value']);
                            $this->db->where('type', $identifierData[$i]['type']);
                            $this->db->where('is_removed', 'false');
                            $this->db->where('resource_id', $resource_id);
                            $this->db->update('identifier');
                        }
                    }
                    //update name table
                    if (isset($nameData)) {
                        $this->db->set('family', $nameData);
                        $this->db->where('is_removed', 'false');
                        $this->db->where('resource_id', $resource_id);
                        $this->db->update('name');
                    }
                
                    //update telecom table
                    if (isset($telecomData) && count($telecomData) > 0) {
                        for ($i = 0; $i < count($telecomData); $i++) {
                            $this->db->set('value', $telecomData[$i]['value']);
                            $this->db->where('system', $telecomData[$i]['system']);
                            $this->db->where('is_removed', 'false');
                            $this->db->where('resource_id', $resource_id);
                            $this->db->update('telecom');
                        }
                    }
                    
                    //update participant table
                    $this->db->select("id");
                    $this->db->from("participant");
                    $this->db->where('resource_id', $resource_id);
                    $this->db->where('is_removed', 'false');
                    $query = $this->db->get();
                    $result = $query->result_array();
                    if (count($result) > 0) {
                        $this->db->where('resource_id', $resource_id);
                        $this->db->delete('participant');
                    }
                    if(isset($participantData) && count($participantData)>0){
                        for ($i = 0; $i < count($participantData); $i++) {
                            $this->db->insert('participant', array('resource_id' => $resource_id, 'data' => json_encode($participantData[$i])));
                        }
                    }
                    
                    http_response_code(200);
                    $response['resourcetype'] = "OperationOutcome";
                    $response['issue']['severity'] = "success";
                    $response['issue']['code'] = "updated";
                    $response['issue']['details']['text'] = "Care team updated successfully";
                } else {
                    http_response_code(400);
                    $response['resourcetype'] = "OperationOutcome";
                    $response['issue']['severity'] = "error";
                    $response['issue']['code'] = "serverissue";
                    $response['issue']['details']['text'] = "Some problem! Please contact to admin.";
                }
            } else {
                http_response_code(400);
                $response['resourcetype'] = "OperationOutcome";
                $response['issue']['severity'] = "error";
                $response['issue']['code'] = "notfound";
                $response['issue']['details']['text'] = "No care team with id: $resource_id";
            }
        } else {
            http_response_code(400);
            $response['resourcetype'] = "OperationOutcome";
            $response['issue']['severity'] = "error";
            $response['issue']['code'] = "notfound";
            $response['issue']['details']['text'] = "Please provide care team id to remove";
        }
        return $response;
    }
    
    function get_careteam_list() {
        $data = [];
        $identifier_data = [];
        $name_data = [];
        $telecom_data = [];
        $query = $this->db->get_where('resource', array('type' => 'CareTeam', 'is_removed' => 'false'));
        $result = $query->result();
        if (isset($result) && count($result)>0) {
            for ($i = 0; $i < count($result); $i++) {
                $resource_id = $result[$i]->id;
                $data[$i]['id'] = $resource_id;
                $data[$i]['resourceType'] = $result[$i]->type;
                $identifier_query = $this->db->get_where('identifier', array('resource_id' => $resource_id, 'is_removed' => 'false'));
                $identifier_result = $identifier_query->result();
                if (isset($identifier_result) && count($identifier_result)>0) {
                    for ($j = 0; $j < count($identifier_result); $j++) {
                        array_push($identifier_data, array('type' => $identifier_result[$j]->type, 'value' => $identifier_result[$j]->value));
                    }
                }
                $name_query = $this->db->get_where('name', array('resource_id' => $resource_id, 'is_removed' => 'false'));
                $name_result = $name_query->result();
                if (isset($name_result) && count($name_result)>0) {
                    for ($j = 0; $j < count($name_result); $j++) {
                        $name_data = $name_result[$j]->family;
                    }
                }
                $telecom_query = $this->db->get_where('telecom', array('resource_id' => $resource_id, 'is_removed' => 'false'));
                $telecom_result = $telecom_query->result();
                if (isset($telecom_result) && count($telecom_result)>0) {
                    for ($j = 0; $j < count($telecom_result); $j++) {
                        array_push($telecom_data, array('system' => $telecom_result[$j]->system, 'value' => $telecom_result[$j]->value));
                    }
                }
                $data[$i]['identifier'] = $identifier_data;
                $data[$i]['name'] = $name_data;
                $data[$i]['telecom'] = $telecom_data;
            }
            return json_encode($data);
        } else {
            return FALSE;
        }
    }

    function delete_careteam($resource_id) {
        if ($resource_id) {
            $this->db->select("*");
            $this->db->from("resource");
            $this->db->where('id', $resource_id);
            $this->db->where('type', 'CareTeam');
            $this->db->where('is_removed', 'false');
            $query = $this->db->get();
            $result = $query->result_array();
            if (isset($result) && count($result) > 0) {
                //update is removed false of resource table
                $this->db->set('is_removed', 'true');
                $this->db->where('type', 'CareTeam');
                $this->db->where('id', $resource_id);
                $this->db->update('resource');
                if ($this->db->affected_rows() > 0) {
                    //update is removed false of identifier table
                    $this->db->set('is_removed', 'true');
                    $this->db->where('resource_id', $resource_id);
                    $this->db->update('identifier');
                    //update is removed false of name table
                    $this->db->set('is_removed', 'true');
                    $this->db->where('resource_id', $resource_id);
                    $this->db->update('name');
                    //update is removed false of telecom table
                    $this->db->set('is_removed', 'true');
                    $this->db->where('resource_id', $resource_id);
                    $this->db->update('telecom');
                    //update is removed false of participant table
                    $this->db->set('is_removed', 'true');
                    $this->db->where('resource_id', $resource_id);
                    $this->db->update('participant');
                    http_response_code(204);
                    $response['resourcetype'] = "OperationOutcome";
                    $response['issue']['severity'] = "success";
                    $response['issue']['code'] = "deleted";
                    $response['issue']['details']['text'] = "Care team removed successfully.";
                } else {
                    http_response_code(404);
                    $response['resourcetype'] = "OperationOutcome";
                    $response['issue']['severity'] = "error";
                    $response['issue']['code'] = "serverissue";
                    $response['issue']['details']['text'] = "Some problem! Please contact to admin.";
                }
            } else {
                http_response_code(400);
                $response['resourcetype'] = "OperationOutcome";
                $response['issue']['severity'] = "error";
                $response['issue']['code'] = "notfound";
                $response['issue']['details']['text'] = "No care team with id: $resource_id";
            }
        } else {
            http_response_code(400);
            $response['resourcetype'] = "OperationOutcome";
            $response['issue']['severity'] = "error";
            $response['issue']['code'] = "notfound";
            $response['issue']['details']['text'] = "Please provide care team id to remove";
        }
        return $response;
    }

}

?>
