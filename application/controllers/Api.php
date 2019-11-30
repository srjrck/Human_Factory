<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Api extends CI_Controller {
  function __construct() {
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE, HEAD");
    parent::__construct();
    $this->load->model('practitionermodel');
    $this->load->model('PatientModel');
    $this->load->model('careteammodel');
    $this->load->model('fakeresourcemodel');
    $this->load->model('mymodel');
  }
  public function _remap($method, $params = array()) {
      //$method = 'process_' . $method;
      if (method_exists($this, $method)) {
          return call_user_func_array(array($this, $method), $params);
      } else {
          $res = $this->fakeresourcemodel->fakeresource($method);
          $response = $this->json($res);
          $this->output->set_content_type('application/fhir+json');
      
          echo $response;
      }
  }
  public function practitioner($id = null) {
      $request_method = $_SERVER['REQUEST_METHOD'];
      $header = $this->input->request_headers();
      if ($request_method == "GET") {
          $res = $this->practitionermodel->get_practitioner($id);
          $response = $this->json($res);
          $this->output->set_content_type('application/fhir+json');
          echo $response;
      } else if ($request_method == "HEAD") {
          $res = $this->practitionermodel->check_practitioner($id);
      } else if ($request_method == "DELETE") {
          $res = $this->practitionermodel->delete_practitioner($id);
          $response = $this->json($res);
          $this->output->set_content_type('application/fhir+json');
          echo $response;
      } else if ($request_method == "POST") {
          if (isset($header['Content-Type']) && ($header['Content-Type'] == 'application/json' || $header['Content-Type'] == 'application/fhir+json')) {
              $data = file_get_contents('php://input');
              $dataArray = json_decode($data, true);
              if (isset($dataArray['resourceType'])) {
                  if (trim($dataArray['resourceType']) == 'Practitioner') {
                      $resouceData = array('type' => $dataArray['resourceType'],'json'=>$data);
                      $identifierData = $dataArray['identifier'];
                      $nameData = $dataArray['name'];
                      $telecomData = $dataArray['telecom'];
                      $res = $this->practitionermodel->create_practitioner($resouceData, $identifierData, $nameData, $telecomData);
                  } else {
                      http_response_code(400);
                      $res['resourcetype'] = "OperationOutcome";
                      $res['issue']['severity'] = "error";
                      $res['issue']['code'] = "TypeError";
                      $res['issue']['details']['text'] = "Cann`t read property 'code' of undefined.";
                  }
              } else {
                  http_response_code(400);
                  $res['resourcetype'] = "OperationOutcome";
                  $res['issue']['severity'] = "error";
                  $res['issue']['code'] = "resourcetype";
                  $res['issue']['details']['text'] = "Sorry! The body that you passes in, is not valid according to practitioner structure.";
              }
          } else {
              http_response_code(400);
              $res['resourcetype'] = "OperationOutcome";
              $res['issue']['severity'] = "error";
              $res['issue']['code'] = "invalid json";
              $res['issue']['details']['text'] = "Sorry! The body that you passes in, is not valid according to practitioner structure.";
          }
          $response = $this->json($res);
          $this->output->set_content_type('application/fhir+json');
          echo $response;
      } else if ($request_method == "PUT") {
          http_response_code(200);
          $res['resourcetype'] = "OperationOutcome";
          $res['issue']['severity'] = "error";
          $res['issue']['code'] = "AssertionError";
          $res['issue']['details']['text'] = "Currently not implemented.";
          $response = $this->json($res);
          $this->output->set_content_type('application/fhir+json');
          echo $response;
      } else {
          http_response_code(404);
          $res['resourcetype'] = "OperationOutcome";
          $res['issue']['severity'] = "error";
          $res['issue']['code'] = "invalid method";
          $res['issue']['details']['text'] = "Please provide http method.";
          $response = $this->json($res);
          $this->output->set_content_type('application/fhir+json');
          echo $response;
      }
  }
  public function careteam($id = null) {
      $request_method = $_SERVER['REQUEST_METHOD'];
      $header = $this->input->request_headers();
      if ($request_method == "GET") {
          $res = $this->careteammodel->get_careteam($id);
          $response = $this->json($res);
          $this->output->set_content_type('application/fhir+json');
          echo $response;
      } else if ($request_method == "HEAD") {
          $res = $this->careteammodel->check_careteam($id);
      } else if ($request_method == "DELETE") {
          $res = $this->careteammodel->delete_careteam($id);
          $response = $this->json($res);
          $this->output->set_content_type('application/fhir+json');
          echo $response;
      } else if ($request_method == "POST") {
          if (isset($header['Content-Type']) && ($header['Content-Type'] == 'application/json' || $header['Content-Type'] == 'application/fhir+json')) {
              $data = file_get_contents('php://input');
              $dataArray = json_decode($data, true);
              if (isset($dataArray['resourceType'])) {
                  if (trim($dataArray['resourceType']) == 'CareTeam') {
                      $resouceData = array('type' => $dataArray['resourceType'],'json'=>$data);
                      $identifierData = $dataArray['identifier'];
                      $nameData = $dataArray['name'];
                      $telecomData = $dataArray['telecom'];
                      $participantData = array();
                      if(isset($dataArray['participant'])){
                      $participantData = $dataArray['participant'];
                      }
                      $res = $this->careteammodel->create_careteam($resouceData, $identifierData, $nameData, $telecomData,$participantData);
                  } else {
                      http_response_code(400);
                      $res['resourcetype'] = "OperationOutcome";
                      $res['issue']['severity'] = "error";
                      $res['issue']['code'] = "TypeError";
                      $res['issue']['details']['text'] = "Cann`t read property 'code' of undefined.";
                  }
              } else {
                  http_response_code(400);
                  $res['resourcetype'] = "OperationOutcome";
                  $res['issue']['severity'] = "error";
                  $res['issue']['code'] = "resourcetype";
                  $res['issue']['details']['text'] = "Sorry! The body that you passes in, is not valid according to care team structure.";
              }
          } else {
              http_response_code(400);
              $res['resourcetype'] = "OperationOutcome";
              $res['issue']['severity'] = "error";
              $res['issue']['code'] = "invalid json";
              $res['issue']['details']['text'] = "Sorry! The body that you passes in, is not valid according to care team structure.";
          }
          $response = $this->json($res);
          $this->output->set_content_type('application/fhir+json');
          echo $response;
      } else if ($request_method == "PUT") {
          if (isset($header['Content-Type']) && ($header['Content-Type'] == 'application/json' || $header['Content-Type'] == 'application/fhir+json')) {
              $data = file_get_contents('php://input');
              $dataArray = json_decode($data, true);
              if (isset($dataArray['resourceType'])) {
                  if (trim($dataArray['resourceType']) == 'CareTeam') {
                      $resouceData = array('type' => $dataArray['resourceType'],'json'=>$data);
                      $identifierData = $dataArray['identifier'];
                      $nameData = $dataArray['name'];
                      $telecomData = $dataArray['telecom'];
                      $participantData = $dataArray['participant'];
                      $res = $this->careteammodel->update_careteam($id,$resouceData, $identifierData, $nameData, $telecomData,$participantData);
                  } else {
                      http_response_code(400);
                      $res['resourcetype'] = "OperationOutcome";
                      $res['issue']['severity'] = "error";
                      $res['issue']['code'] = "TypeError";
                      $res['issue']['details']['text'] = "Cann`t read property 'code' of undefined.";
                  }
              } else {
                  http_response_code(400);
                  $res['resourcetype'] = "OperationOutcome";
                  $res['issue']['severity'] = "error";
                  $res['issue']['code'] = "resourcetype";
                  $res['issue']['details']['text'] = "Sorry! The body that you passes in, is not valid according to care team structure.";
              }
          } else {
              http_response_code(400);
              $res['resourcetype'] = "OperationOutcome";
              $res['issue']['severity'] = "error";
              $res['issue']['code'] = "invalid json";
              $res['issue']['details']['text'] = "Sorry! The body that you passes in, is not valid according to care team structure.";
          }
          $response = $this->json($res);
          $this->output->set_content_type('application/fhir+json');
          echo $response;
      } else {
          http_response_code(200);
          $res['resourcetype'] = "OperationOutcome";
          $res['issue']['severity'] = "error";
          $res['issue']['code'] = "invalid method";
          $res['issue']['details']['text'] = "Please provide http method.";
          $response = $this->json($res);
          $this->output->set_content_type('application/fhir+json');
          echo $response;
      }
  }
  public function mycontroller($id = null) {
    $res = $this->mymodel->get_data($id);
    $response = $this->json($res);
    $this->output->set_content_type('application/fhir+json');
    echo $response;
  }
  private function json($data) {
      if (is_array($data)) {
          return json_encode($data);
      }
  }
  /*Patient API*/
  public function Patient($id = null){
    $request_method = $_SERVER['REQUEST_METHOD'];
    $header = $this->input->request_headers();
    if ($request_method == "GET") {
      $res = $this->PatientModel->get_patient($id);
      $response = $this->json($res);
      $this->output->set_content_type('application/fhir+json');
      echo $response;
    }else if ($request_method == "HEAD") {
      $res = $this->PatientModel->check_patient($id);
      echo $response;
    }else if($request_method == "DELETE"){
      $res = $this->PatientModel->delete_patient($id);
      $response = $this->json($res);
      $this->output->set_content_type('application/fhir+json');
      echo $response;
    }else if($request_method == "POST") {
      if (isset($header['Content-Type']) && ($header['Content-Type'] == 'application/json' || $header['Content-Type'] == 'application/fhir+json')) {
        $data = file_get_contents('php://input');
        $dataArray = json_decode($data, true);
        if (isset($dataArray['resourceType'])) {
          if (trim($dataArray['resourceType']) == 'Patient') {
            $resouceData = array('type' => $dataArray['resourceType'],'json'=>$data);
            $identifierData = $dataArray['identifier'];
            $nameData = $dataArray['name'];
            $telecomData = $dataArray['telecom'];
            $res = $this->PatientModel->create_patient($resouceData, $identifierData, $nameData, $telecomData);
          }else{
            http_response_code(400);
            $res['resourcetype'] = "OperationOutcome";
            $res['issue'][0]['severity'] = "error";
            $res['issue'][0]['code'] = "TypeError";
            $res['issue'][0]['details']['text'] = "Cann`t read patient 'code' of undefined.";
          }
        }else{
          http_response_code(400);
          $res['resourcetype'] = "OperationOutcome";
          $res['issue'][0]['severity'] = "error";
          $res['issue'][0]['code'] = "resourcetype";
          $res['issue'][0]['details']['text'] = "Sorry! The body that you passes in, is not valid according to care team structure.";
        }
      }else{
        http_response_code(400);
        $res['resourcetype'] = "OperationOutcome";
        $res['issue'][0]['severity'] = "error";
        $res['issue'][0]['code'] = "invalid json";
        $res['issue'][0]['details']['text'] = "Sorry! The body that you passes in, is not valid according to practitioner structure.";
      }
      $response = $this->json($res);
      $this->output->set_content_type('application/fhir+json');
      echo $response;
    }else if ($request_method == "PUT") {
      http_response_code(200);
      $res['resourcetype'] = "OperationOutcome";
      $res['issue'][0]['severity'] = "error";
      $res['issue'][0]['code'] = "AssertionError";
      $res['issue'][0]['details']['text'] = "Currently not implemented.";
      $response = $this->json($res);
      $this->output->set_content_type('application/fhir+json');
      echo $response;
    }else{
      http_response_code(200);
      $res['resourcetype'] = "OperationOutcome";
      $res['issue'][0]['severity'] = "error";
      $res['issue'][0]['code'] = "invalid method";
      $res['issue'][0]['details']['text'] = "Please provide http method.";
      $response = $this->json($res);
      $this->output->set_content_type('application/fhir+json');
      echo $response;
    }
  }
}