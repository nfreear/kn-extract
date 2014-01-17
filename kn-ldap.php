<?php
/**
  Knowledge-Network LDAP.

  Workspace "owners" (top-level only) and document owners,
  filtered by those that are still in the

  "owners" -- Workspace editors(/ authors); Document authors.

  Orphaned documents (ones with no current OU authors..)

--

  @author    Nick Freear, 17 Dec 2013.
  @copyright Copyright 2013 Nick Freear, The Open University.
*/
require "./ldap.php";

define('OUCU_COLUMN', 1);
define('EMAIL_COLUMN', 4);
define('DATE_COLUMN', 5);
$csv_file = 'T:/KN-users-ou-visit-2013-11-13.csv';


echo $argv[1];
#echo basename(__FILE__);

//$is_library = (basename($argv[0]) !== basename(__FILE__));
//$obj = csv_to_array($csv_file);


$csv_raw = file($csv_file);
$obj = array();


$ldap = new LdapOU($ldaprdn, $ldappass);

foreach ($csv_raw as $row) {
  // "UCS-2" to utf-8.
  $csv_str = mb_convert_encoding($row, 'UTF-8', 'UCS-2');

  $row = csv_to_obj(str_getcsv($csv_str));

  $ldap_query = '(samaccountname=' . $row->oucu . ')';
  $count = $ldap->search($ldap_query, LdapOU::$attrs_ou);

  $row->count = $count;

  $obj[] = $row;

  if (count($obj) > 10) {
    break;
  }
}

var_dump($obj);



function csv_to_obj($row) {
  return (object) array(
    'oucu' => $row[OUCU_COLUMN],
	'email' => $row[EMAIL_COLUMN],
	'date' => $row[DATE_COLUMN],
  );
}

/**
 * @link http://gist.github.com/385876
 */
function csv_to_array($filename='', $delimiter=',')
{
    if(!file_exists($filename) || !is_readable($filename))
        return FALSE;

    $header = NULL;
    $data = array();
    if (($handle = fopen($filename, 'r')) !== FALSE)
    {
        while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
        {
            if(!$header)
                $header = $row;
            else
                $data[] = array_combine($header, $row);
        }
        fclose($handle);
    }
    return $data;
}

//End.
