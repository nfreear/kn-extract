<?php
/**
  Knowledge-Network data extraction - with LDAP.

  Workspace "owners" (top-level only) and document owners,
  filtered by those that are still in the

  "owners" -- Workspace editors(/ authors); Document authors.

  Orphaned documents (ones with no current OU authors..)

  @copyright Copyright 2013 Nick Freear, The Open University.
  @author    Nick Freear, 17 Dec 2013.
*/
require_once './config.php';
require_once './lib/LdapOU.php';


$csv_in = $argc > 1 ? $argv[$argc - 1] : CSV_FILE;
$csv_out = './'. basename($csv_in, '.csv') .'-out.csv';
$php_out = './'. basename($csv_in, '.csv') .'-php-serial.txt';
$json_out = './'. basename($csv_in, '.csv') .'-json.txt';
$email_out= './'. basename($csv_in, '.csv') .'-email.txt';


//$is_library = (basename($argv[0]) !== basename(__FILE__));
//$obj = csv_to_array($csv_file);

$csv_raw = @file($csv_in);
if (! $csv_raw) {
  echo "Error, failed to open file: $csv_in". PHP_EOL;
  exit;
}
echo $csv_in;


$obj = array();
$email_list = '';

$fcsv = fopen($csv_out, 'w');

$ldap = new LdapOU(LDAP_RDN, LDAP_PASS);

foreach ($csv_raw as $row) {
  // "UCS-2" to utf-8.
  $csv_str = mb_convert_encoding($row, 'UTF-8', 'UCS-2');

  $row = csv_to_obj(str_getcsv($csv_str));

  if (! $row) break;

  $ldap_query = '(samaccountname=' . $row->oucu . ')';
  $count = $ldap->search($ldap_query, LdapOU::$attrs_ou);

  $row->is_current = $count;
  $obj[] = $row;

  $bytes = fputcsv($fcsv, (array)$row);

  if ($row->is_current) {
    $email_list .= $row->email . EMAIL_SEP;
  }

  if (LIMIT && count($obj) > LIMIT) {
    break;
  }
}

fclose($fcsv);
$bytes = file_put_contents($email_out, $email_list);
#$bytes = file_put_contents($json_out, json_encode($obj));
$bytes = file_put_contents($php_out, serialize($obj));

echo "Files written, $bytes bytes: ". $php_out .PHP_EOL;
#var_dump($obj);




function csv_to_obj($row) {
  if (! isset($row[0])) return NULL;

  return (object) array(
    'user_id' => $row[COLUMN_USER_ID],
    'oucu'    => $row[COLUMN_OUCU],
    'firstname' => $row[COLUMN_FIRSTNAME],
    'surname' => $row[COLUMN_SURNAME],
    'email'   => $row[COLUMN_EMAIL],
    'date'    => $row[COLUMN_DATE],
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
