<?php

/**
 * Class CRM_BeltoolApi_Contact
 * Contains custom Contact API methods.
 */
class CRM_BeltoolApi_Contact {

  /**
   * GetBeltoolData
   * @param array $params API call parameters
   * @return array Array of contacts
   * @throws Exception Throws exceptions on error
   */
  public static function getBeltoolData(&$params) {

    // Get ACL
    $tables = ['civicrm_contact'];

    // Include everyone except those who are deleted or those who are deceased
    $whereClause = 'contact.is_deleted = "0" AND contact.is_deceased = "0"';

    // Add contact id to where clause if defined
    if (!empty($params['contact_id'])) {
      $whereClause = " contact.id = " . (int)$params['contact_id'] . " AND " . $whereClause;
    }

    $groupJoin = '';
    if (!empty($params['group'])) {
      $groupIds = $params['group'];
      if (!is_array($groupIds)) {
        $groupIds = array($groupIds);
      }
      $groupJoin = "INNER JOIN civicrm_group_contact ON contact.id = civicrm_group_contact.contact_id AND civicrm_group_contact.status = 'Added' AND civicrm_group_contact.group_id IN (".implode(", ", $groupIds).") ";
    }

    if (!empty($params['group_contact_id_offset'])) {
      //$whereClause = " civicrm_group_contact.id > " . (int)$params['group_contact_id_offset'] . " AND " . $whereClause;
    }

    // Other data used to enrich this export
    $genderCodes = \CRM_Core_PseudoConstant::get('CRM_Contact_DAO_Contact', 'gender_id');
    $spGeoNames = static::getSPGeostelselNames();

    // Execute contact query (civicrm_contact and all data that is tied directly to a single civicrm_contact record))
    $query = <<<SQL
SELECT
  contact.id AS contact_id, first_name, middle_name, last_name, display_name, gender_id, birth_date, do_not_mail, do_not_phone, do_not_email, do_not_sms, is_opt_out,
  caddr.postal_code,
  cphone.phone AS phone, cmobile.phone AS mobile, cemail.email AS email,
  country.name AS country_name, country.iso_code AS country_code,
  geostelsel.afdeling AS afdeling_code, geostelsel.regio AS regio_code, geostelsel.provincie AS provincie_code,
  civicrm_group_contact.id AS group_contact_id
  FROM civicrm_contact contact
  LEFT JOIN civicrm_group_contact `civicrm_group_contact-ACL` ON contact.id = `civicrm_group_contact-ACL`.contact_id
  LEFT JOIN civicrm_membership membership_access ON contact.id = membership_access.contact_id
  LEFT JOIN civicrm_value_geostelsel geostelsel ON contact.id = geostelsel.entity_id
  LEFT JOIN civicrm_value_migratie_1 cmigr ON contact.id = cmigr.entity_id
  LEFT JOIN civicrm_address caddr ON contact.id = caddr.contact_id AND caddr.is_primary = 1
  LEFT JOIN civicrm_country country ON caddr.country_id = country.id
  LEFT JOIN civicrm_value_adresgegevens_12 caddrx ON caddr.id = caddrx.entity_id
  LEFT JOIN civicrm_phone cphone ON contact.id = cphone.contact_id AND cphone.phone_type_id = 1
  LEFT JOIN civicrm_phone cmobile ON contact.id = cmobile.contact_id AND cmobile.phone_type_id = 2
  LEFT JOIN civicrm_email cemail ON contact.id = cemail.contact_id AND cemail.is_primary = 1
  {$groupJoin}
  WHERE {$whereClause}
  ORDER BY civicrm_group_contact.id ASC, contact.id ASC LIMIT {$params['options']['offset']},{$params['options']['limit']}
SQL;

print($query);
    // return civicrm_api3_create_error(['query' => $query]);
    $cres = \CRM_Core_DAO::executeQuery($query);

    // Store contacts, and get all contact ids for the next query
    $contacts = [];
    $cids = [];

    /** @var \DB_DataObject $cres */
    while ($cres->fetch()) {

      // Get contact array
      $contact = static::daoToArray($cres);

      // Enrich contact data
      $contact['afdeling'] = $spGeoNames[$contact['afdeling_code']];
      $contact['regio'] = $spGeoNames[$contact['regio_code']];
      $contact['provincie'] = $spGeoNames[$contact['provincie_code']];
      $contact['gender'] = $genderCodes[$contact['gender_id']];

      // Add to contacts and cids array
      $contacts[$cres->contact_id] = $contact;
      $cids[] = $cres->contact_id;
    }

    $cidlist = implode(',', $cids);
    if ($cres instanceof \CRM_Core_DAO) {
      $cres->free();
    }

    // Return contacts
    return $contacts;
  }

  /**
   * Set custom permissions per API method here (called from BeltoolApi.php)
   * @param array $permissions API permissions array
   */
  public static function alterAPIPermissions(&$permissions = []) {
    $permissions['contact']['getbeltooldata'] = ['access CiviCRM'];
  }

  /**
   * Get an array of names of SP afdelingen / regio's / provincies
   * @return array Array of SP geostelsel names
   */
  private static function getSPGeostelselNames() {

    $res = \CRM_Core_DAO::executeQuery("SELECT id, display_name FROM civicrm_contact WHERE contact_sub_type IN ('SP_Landelijk','SP_Provincie','SP_Regio','SP_Afdeling','SP_Werkgroep')");
    $ret = [];

    while ($res->fetch()) {
      $ret[$res->id] = str_ireplace(['SP-afdeling ', 'SP-werkgroep ', 'SP-regio ', 'SP-provincie '], '', $res->display_name);
    }

    return $ret;
  }

  /**
   * Convert DAO object to an array, removing private properties
   * (is there a better way to get all properties without having to specify them individually?)
   * @param \DB_DataObject $object Data object
   * @return array Data array
   */
  private static function daoToArray($object) {
    $ret = (array) $object;
    foreach ($ret as $k => $v) {
      if (substr($k, 0, 1) == '_' || $k == 'N' || !isset($v) || $v === "") {
        unset($ret[$k]);
      }
    }
    return $ret;
  }
}
