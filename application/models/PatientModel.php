<?php
defined('BASEPATH') OR exit('No direct script access allowed');
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
        if(isset($nameData) && count($nameData) > 0) {
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
        $response['issue'][0]['severity'] = "success";
        $response['issue'][0]['code'] = "created";
        $response['issue'][0]['details']['text'] = "Patient created successfully";
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
      $response['issue'][0]['severity'] = "error";
      $response['issue'][0]['code'] = "resourcetype";
      $response['issue'][0]['details']['text']="Sorry! The body that you passes in, is not valid according to practitioner structure.";
    }
    return $response;
  }
  function get_patient($id){
    $response = [];
    $this->db->select("id,type");
    $this->db->from("resource");
    $this->db->where('id', $id);
    $this->db->where('type', 'Patient');
    $this->db->where('is_removed', 'false');
    $query = $this->db->get();
    $result = $query->result_array();
    if (isset($result) && count($result) > 0) {
      $identifier_data = [];
      $name_data = [];
      $telecom_data = [];
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
        for ($j = 0; $j < count($name_result); $j++) {
          array_push($name_data, array('family' => $name_result[$j]->family, 'given' => $name_result[$j]->given));
        }
      }
      $telecom_query = $this->db->get_where('telecom', array('resource_id' => $result[0]['id'], 'is_removed' => 'false'));
      $telecom_result = $telecom_query->result();
      if (isset($telecom_result) && count($telecom_result)>0) {
        for ($j = 0; $j < count($telecom_result); $j++) {
          array_push($telecom_data, array('system' => $telecom_result[$j]->system, 'value' => $telecom_result[$j]->value));
        }
      }
      $data['id'] = $result[0]['id'];
      $data['type'] = $result[0]['type'];
      $data['identifier'] = $identifier_data;
      $data['name'] = $name_data;
      $data['telecom'] = $telecom_data;
      http_response_code(200);
      $response['status'] = "success";
      $response['msg'] = "Data retrieved successfully";
      $response['data'] = $data;
    } else {
      http_response_code(404);
      $response['resourcetype'] = "OperationOutcome";
      $response['issue'][0]['severity'] = "error";
      $response['issue'][0]['code'] = "notfound";
      $response['issue'][0]['details']['text'] = "No such patient with id: $id";
    }
    return $response;
  }
  function check_patient($id) {
    $this->db->select("id,type");
    $this->db->from("resource");
    $this->db->where('id', $id);
    $this->db->where('type', 'Patient');
    $this->db->where('is_removed', 'false');
    $query = $this->db->get();
    $result = $query->result_array();
    if (isset($result) && count($result) > 0) {
      http_response_code(200);
    }else {
      http_response_code(404);
    }
  }
  function delete_patient($resource_id) {
    if ($resource_id) {
      $this->db->select("*");
      $this->db->from("resource");
      $this->db->where('id', $resource_id);
      $this->db->where('type', 'Patient');
      $this->db->where('is_removed', 'false');
      $query = $this->db->get();
      $result = $query->result_array();
      if (isset($result) && count($result) > 0) {
        //update is removed false of resource table
        $this->db->set('is_removed', 'true');
        $this->db->where('type', 'Patient');
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
          http_response_code(204);
          $response['resourcetype'] = "OperationOutcome";
          $response['issue'][0]['severity'] = "success";
          $response['issue'][0]['code'] = "removed";
          $response['issue'][0]['details']['text'] = "patient removed successfully";
        } else {
          http_response_code(404);
          $response['resourcetype'] = "OperationOutcome";
          $response['issue'][0]['severity'] = "error";
          $response['issue'][0]['code'] = "serverissue";
          $response['issue'][0]['details']['text'] = "Some problem! Please contact to admin.";
        }
      } else {
        http_response_code(404);
        $response['resourcetype'] = "OperationOutcome";
        $response['issue'][0]['severity'] = "error";
        $response['issue'][0]['code'] = "notfound";
        $response['issue'][0]['details']['text'] = "No patient found with id: $resource_id";
      }
    } else {
      http_response_code(400);
      $response['resourcetype'] = "OperationOutcome";
      $response['issue'][0]['severity'] = "error";
      $response['issue'][0]['code'] = "notfound";
      $response['issue'][0]['details']['text'] = "Please provide patient id to remove";
    }
    return $response;
  }
}
?>