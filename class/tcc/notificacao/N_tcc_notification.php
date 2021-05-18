<?php
// This file is NOT a part of Moodle - http://moodle.org/
//
// This client for Moodle 2 is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//

/**
 * XML-RPC client for Moodle 2
 *
 * @authorr Jerome Mouneyrac
 */


function notificar($title = "", $summary="", $content="", $category="", $redirect_key="", $user_codes=""){
  $autorization = ' ';
  /// use your domain here:
  $domainname   = ' ';
  /// PARAMETERS
  $params = new stdClass;
  $params->title  = $title;
  $params->summary = $summary;
  $params->content = $content;
  $params->category = $category;
  //$params->redirect_key = $redirect_key;
  $params->user_codes = $user_codes;


  /// REST CALL
  header('Content-Type: application/json');
  $serverurl = $domainname;

  $curl = new curlNova;
  $options['CURLOPT_HTTPHEADER'] = array($autorization );


  $resp = $curl->post($serverurl, $params, $options );
  return $resp;
}
