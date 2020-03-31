<?php

/**
 * API Method Parameters
 * @return array
 */
function _civicrm_api3_contact_getbeltooldata_params() {
  return [
    'contact_id'            => ['api.required' => 0, 'name' => 'contact_id',
                                'title' => 'Contact ID (int)', 'type' => CRM_Utils_Type::T_INT],
    'group'              => ['api.required' => 0, 'name' => 'group',
                                'title' => 'Group', 'type' => CRM_Utils_Type::T_INT,
                                'pseudoconstant' => array(
                                    'table' => 'civicrm_group',
                                    'keyColumn' => 'id',
                                    'labelColumn' => 'title'
                                )],
    'get_count' => ['api.required' => 0, 'name' => 'get_count',
                                 'title' => 'Count contacts', 'type' => CRM_Utils_Type::T_BOOLEAN],
    'group_contact_id_offset' => ['api.required' => 0, 'name' => 'group_contact_id_offset',
                                 'title' => 'Group contact id offset', 'type' => CRM_Utils_Type::T_INT],
  ];
}

/**
 * Contact.GetBeltoolData API specification
 * This is used for documentation and validation.
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 * @param array $params description of fields supported by this API call
 * @return void
 */
function _civicrm_api3_contact_getbeltooldata_spec(&$params) {
  $myparams = _civicrm_api3_contact_getbeltooldata_params();
  $params = array_merge($params, $myparams);
}

/**
 * Contact.GetBeltoolData API
 * Returns detailed information about contacts that currently are a member of SP and/or ROOD
 * and that are accessible to the user using the API,
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @throws API_Exception
 */
function civicrm_api3_contact_getbeltooldata($params) {

  // Parse booleans and options
  $params['get_count'] = ($params['get_count'] == 1);
  $params['group_contact_id_offset'] = !empty($params['group_contact_id_offset']) ? (int) $params['group_contact_id_offset'] : 0;
  $params['options']['limit'] = !empty($params['options']['limit']) ? (int) $params['options']['limit'] : 25;
  $params['options']['offset'] = !empty($params['options']['offset']) ? (int) $params['options']['offset'] : 0;

  if(isset($params['contact_id'])) {
    if(is_array($params['contact_id']) || !is_numeric($params['contact_id'])) {
      return civicrm_api3_create_error('Invalid parameter: contact_id is not a number.');
    }
    $params['contact_id'] = (int)$params['contact_id'];
  }

  if (isset($params['group_contact_id_offset'])) {
    if (empty($params['group'])) {
      return civicrm_api3_create_error('Cannot set group contact id offset when no groups are set.');
    }
  }

  // Get and return data
  // (Exceptions should be automatically caught by the API handler)
  $result = CRM_BeltoolApi_Contact::getBeltoolData($params);
  return civicrm_api3_create_success($result, $params, 'Contact', 'getbeltooldata');
}
