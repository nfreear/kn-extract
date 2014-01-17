<?php
/**
  Configuration for Knowledge-Network data extraction.
*/

// using ldap bind
define('LDAP_RDN',  'username');  // ldap rdn or dn
define('LDAP_PASS', 'password');  // associated password

#$ldaprdn = 'uname'; // ldap rdn or dn
#$ldappass= 'password';  // associated password

define('CSV_FILE', 'T:/KN-users-ou-visit-2013-11-13.csv');
define('LIMIT', 10);

define('COLUMN_USER_ID', 0);
define('COLUMN_OUCU',    1);
define('COLUMN_FIRSTNAME',2);
define('COLUMN_SURNAME', 3);
define('COLUMN_EMAIL',   4);
define('COLUMN_DATE',    5);
define('COLUMN_IS_CURRENT',6);


//End.
