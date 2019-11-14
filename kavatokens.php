<?php

require_once 'kavatokens.civix.php';
use CRM_Kavatokens_ExtensionUtil as E;

function kavatokens_civicrm_tokens(&$tokens) {
  if (isset($tokens['kavatokens'])) {
    return;
  }

  $tokens['kavatokens'] = [
    'kavatokens.aponame' => 'Apotheek naam',
    'kavatokens.apoaddress' => 'Apotheek huidig adres',
    'kavatokens.apopreviousaddress' => 'Apotheek vorig adres',
    'kavatokens.apopreviousaddressdate' => 'Apotheek afsluitdatum vorig adres',
    'kavatokens.apbnumber' => 'APB-nummer',
    'kavatokens.acquisitionnumber' => 'Overname nummer',
    'kavatokens.tarif' => 'Tariferingsdienst',
    'kavatokens.tarifdate' => 'Datum toetreding tariferingsdienst',
    'kavatokens.ordernumber' => 'Nummer orde der apothekers',
  ];
}


function kavatokens_civicrm_tokenValues(&$details, $contactIDs, $jobID, $tokens, $className) {
  if (array_key_exists('kavatokens', $tokens)) {
    foreach ($contactIDs as $contactID) {
      kavatokens_get_token_info($contactID, $details);
    }
  }
}

function kavatokens_get_token_info($contactID, &$details) {
  // get the data of the pharmacy this person is "titularis" of
  $sql = "
    select
      c.organization_name pharmacy_name
      , ctd.organization_name tariferingsdienst 
      , td.start_date tariferingsdienst_start
      , concat(a.street_address, ', ', a.postal_code, ' ', a.city) current_address
      , concat(pa.straat_279, ', ', pa.postcode_280, ' ', pa.gemeente_281) previous_address
      , DATE_FORMAT(pa.wijzigingsdatum_282, '%d/%m%/%Y') previous_address_date
      , uitb.apb_nummer_43 apb_number
      , uitb.overname_44 aquisition_number
      , ap.inschrijvingsnr_orde_74 order_number
    from
      civicrm_relationship r 
    inner join
      civicrm_contact c on r.contact_id_a = c.id and r.relationship_type_id = 35
    left outer join
      civicrm_address a on a.contact_id = c.id and a.location_type_id = 2
    left outer join
      civicrm_value_vorig_adres_88 pa on pa.entity_id = a.id
    left outer join
      civicrm_value_contact_apotheekuitbating uitb on uitb.entity_id = c.id
    left outer join
      civicrm_value_contact_apotheker ap on ap.entity_id = r.contact_id_b
    left outer join
      civicrm_relationship td on r.contact_id_a = td.contact_id_a and td.relationship_type_id = 36 and td.is_active = 1
    left outer join
      civicrm_contact ctd on td.contact_id_b = ctd.id
    WHERE
      r.contact_id_b = $contactID
    and
      r.is_active = 1
    order by
      a.is_primary desc  
  ";
  $dao = CRM_Core_DAO::executeQuery($sql);
  if ($dao->fetch()) {
    $details[$contactID]['kavatokens.aponame'] = $dao->pharmacy_name;
    $details[$contactID]['kavatokens.apoaddress'] = $dao->current_address;
    $details[$contactID]['kavatokens.apopreviousaddress'] = $dao->previous_address;
    $details[$contactID]['kavatokens.apopreviousaddressdate'] = $dao->previous_address_date;
    $details[$contactID]['kavatokens.apbnumber'] = $dao->apb_number;
    $details[$contactID]['kavatokens.acquisitionnumber'] = $dao->aquisition_number;
    $details[$contactID]['kavatokens.tarif'] = $dao->tariferingsdienst;
    $details[$contactID]['kavatokens.tarifdate'] = $dao->tariferingsdienst_start;
    $details[$contactID]['kavatokens.ordernumber'] = $dao->order_number;
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function kavatokens_civicrm_config(&$config) {
  _kavatokens_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function kavatokens_civicrm_xmlMenu(&$files) {
  _kavatokens_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function kavatokens_civicrm_install() {
  _kavatokens_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function kavatokens_civicrm_postInstall() {
  _kavatokens_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function kavatokens_civicrm_uninstall() {
  _kavatokens_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function kavatokens_civicrm_enable() {
  _kavatokens_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function kavatokens_civicrm_disable() {
  _kavatokens_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function kavatokens_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _kavatokens_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function kavatokens_civicrm_managed(&$entities) {
  _kavatokens_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function kavatokens_civicrm_caseTypes(&$caseTypes) {
  _kavatokens_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function kavatokens_civicrm_angularModules(&$angularModules) {
  _kavatokens_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function kavatokens_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _kavatokens_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function kavatokens_civicrm_entityTypes(&$entityTypes) {
  _kavatokens_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function kavatokens_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function kavatokens_civicrm_navigationMenu(&$menu) {
  _kavatokens_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _kavatokens_civix_navigationMenu($menu);
} // */
