<?php

  /*
  ** @v 0.5
  ** @author: aphichat panjamanee
  ** Location: /wp-content/themes/<theme>/functions.php
  */

  # https://www.gravityhelp.com/documentation/article/gform_after_submission/
  # x = form id
  add_action('gform_after_submission_x', 'push', 10, 2);

  function push($entry, $form)
  {

    # Teamleader group ID
    $group = 0;
    $secret = '';

    # https://www.gravityhelp.com/documentation/article/rgar/
    # Build up vars from form [POST]

    $company = rgar($entry, '');
    $email = rgar($entry, '');
    $telephone = rgar($entry, '');
    $surname = rgar($entry, '');
    $price = rgar($entry, '');
    $description = rgar($entry, '');

    # Create company
    $companyFields = array(
      'api_group' => ($group),
      'api_secret'=>($secret),
      'name'=>($company),
      'email'=>($email),
      'telephone'=>($telephone),
    );

    # Create contacts
    $contactFields = array(
      'api_group' => ($group),
      'api_secret'=>($secret),
      'forename'=>'',
      'surname'=>($surname),
      'email'=>($email),
    );

    # Create deal
    $dealFields = array(
      'api_group'           => ($group),
      'api_secret'          => ($secret),
      'price_1'             => (floatval($price)),
      'amount_1'            => 1,
      'vat_1'               => '00',
      'title'               => 'GF - entry from website',
      'source'              => 'Website',
      'contact_or_company'  =>'company',
      'description_1'       =>($description),
      # xxxx = teamleader custom-field number
      'custom_field_xxxx'   =>($translate_from),
    );

    $contactID = '';

    # Fire field to Team Leader

    $companyID = doCurl('https://www.teamleader.be/api/addCompany.php', $companyFields, '', '');

    if($companyID != '') {
      $contactID = doCurl('https://www.teamleader.be/api/addContact.php', $contactFields, $companyID, '');
    }

    $update = doCurl('https://www.teamleader.be/api/updateContact.php', $contactFields, $companyID, $contactID);

    if($update == 'OK')
    {
      $dealFields['contact_or_company_id'] = $companyID;
      $addDeal = doCurl('https://www.teamleader.be/api/addDeal.php', $dealFields, '', '');
    }
  }

  # Curl function
  function doCurl($url, $fields, $id, $id2)
  {
    if($id != '') {
      $fields['linked_company_ids'] = $id;
    }

    if($id2 != '') {
      $fields['contact_id'] = $id2;
      $fields['track_changes'] = '0';
    }
    // Make the POST request using Curl
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, true); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

    // Decode and display the output
    $api_output =  curl_exec($ch);
    $json_output = json_decode($api_output);
    $output = $json_output ? $json_output : $api_output;

    // Clean up
    curl_close($ch);

    return $output;
  }
