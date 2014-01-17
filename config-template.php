<?php
/**
  Configuration for the Knowledge-Network data extraction tools.
*/

// using ldap bind
define('LDAP_RDN',  'username');  // ldap rdn or dn
define('LDAP_PASS', 'password');  // associated password

#$ldaprdn = 'uname'; // ldap rdn or dn
#$ldappass= 'password';  // associated password

define('CSV_FILE', 'T:/KN-users-ou-visit-2013-11-13.csv');
define('LIMIT', 10);  // 10, or FALSE.
// http://stackoverflow.com/questions/12120190/what-is-the-best-separator-to-separate-multiple-emails
// RFC 6068 -- ','
define('EMAIL_SEP', ';' . "\n");

// Input CSV columns.
define('COLUMN_USER_ID', 0);
define('COLUMN_OUCU',    1);
define('COLUMN_FIRSTNAME',2);
define('COLUMN_SURNAME', 3);
define('COLUMN_EMAIL',   4);
define('COLUMN_DATE',    5);   // Last visited (not created) date.
define('COLUMN_IS_CURRENT',6); // Output only.


//End.
