<?php
/**
  Knowledge-Network data extraction - with LDAP.

  Workspace "owners" (top-level only) and document owners,
  filtered by those that are still in the

  "owners" -- Workspace editors(/ authors); Document authors.

  Orphaned documents (ones with no current OU authors..)


  C:\Users\ndf42\workspace\kn>\Users\ndf42\xampp\php\php kn-ldap.php  T:\KN-users-ou-doc-authors-2014-01-17.csv

  @copyright Copyright 2013 Nick Freear, The Open University.
  @author    Nick Freear, 17 December 2013.
*/
require_once './config.php';
require_once './lib/LdapOU.php';


$csv_in = $argc > 1 ? $argv[$argc - 1] : CSV_FILE;
$csv_out = './'. basename($csv_in, '.csv') .'-out.csv';
$php_out = './'. basename($csv_in, '.csv') .'-php-serial.txt';
$json_out = './'. basename($csv_in, '.csv') .'-json.txt';
$email_out= './'. basename($csv_in, '.csv') .'-email.txt';
$sql_out = './'. basename($csv_in, '.csv') .'-update.sql';


//$is_library = (basename($argv[0]) !== basename(__FILE__));
//$obj = csv_to_array($csv_file);

$csv_raw = @file($csv_in);
if (! $csv_raw) {
  echo "Error, failed to open file: $csv_in". PHP_EOL;
  exit;
}
echo $csv_in;


$obj = $email_list = $user_ids = array();

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
    $email_list[] = $row->email;
  } else {
    $user_ids[] = $row->user_id;
  }

  if (LIMIT && count($obj) > LIMIT) {
    break;
  }
}

fclose($fcsv);
$bytes = file_put_contents($email_out, implode(EMAIL_SEP, $email_list));
#$bytes = file_put_contents($json_out, json_encode($obj));
$bytes = file_put_contents($sql_out, sql_tbluser_update($user_ids));
$bytes = file_put_contents($php_out, serialize($obj));  //? serialize((array) $obj)

echo "Files written, $bytes bytes: ". $php_out .PHP_EOL;
echo "Current authors: ". count($email_list) .PHP_EOL;
#var_dump($obj);




function csv_to_obj($row) {
  if (! isset($row[0])) return NULL;

  return (object) array(
    'user_id' => COLUMN_USER_ID < 0 ? NULL : $row[COLUMN_USER_ID],
    'oucu'    => COLUMN_OUCU  < 0 ? NULL : $row[COLUMN_OUCU],
    'firstname' => COLUMN_FIRSTNAME < 0 ? NULL : $row[COLUMN_FIRSTNAME],
    'surname' => COLUMN_SURNAME < 0 ? NULL : $row[COLUMN_SURNAME],
    'email'   => COLUMN_EMAIL < 0 ? NULL : $row[COLUMN_EMAIL],
    'date'    => COLUMN_DATE  < 0 ? NULL : $row[COLUMN_DATE],
  );
}

/**
 * @link https://gist.github.com/jaywilliams/385876
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

/** Return MSSQL UPDATE statement.
 * @link http://www.karlrixon.co.uk/writing/update-multiple-rows-with-different-values-and-a-single-sql-query/
 */
function sql_tbluser_update($user_ids) {
  $user_ids = wordwrap(implode(', ', $user_ids), 80);
  $mssql_template = <<<EOT
--
-- Auto-generated: {DATE}
--

USE [knowledge_network];

ALTER TABLE [dbo].[tblUser] ADD isCurrent int NOT NULL DEFAULT(1);

UPDATE tblUser SET isCurrent = 0 WHERE UserID = 1;


--UPDATE [dbo].[tblUser]
--    SET isCurrent = CASE UserID
--        WHEN 1 THEN 0
--        WHEN 2 THEN 1
--        WHEN 3 THEN 0
--    END
--WHERE UserID IN (1,2,3)

EOT;

  return strtr($mssql_template, array(
      'WHERE UserID = 1' => 'WHERE UserID in ('.PHP_EOL. $user_ids .PHP_EOL.')',
      '{DATE}' => date('r')
  ));
}

//End.
