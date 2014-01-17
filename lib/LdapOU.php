<?php
/**
 OU LDAP library and tests.

 http://uk2.php.net/ldap

 @copyright Copyright 2008-2014 Nick Freear, The Open University.
 @author    N.D.Freear, 4 Sep 2008, 2 dec 2010.
*/
#header('Content-Type: text/plain; charset=UTF-8');

$is_library = (basename($argv[0]) !== basename(__FILE__));

if (!$is_library):

// $person is all or part of a person's name, eg "Jo"
#$person = 'Freear';
#$person = '***';

#$person = 'Page';

#$CW_foaf_name = 'Anna Page';
$CW_foaf_name = 'Carol Ruditis';

$CW_foaf_mbox_sha1 = sha1(strtolower("mailto:A.C.Page@open.ac.uk")); #'b8155c18ae76f8bae2ffc2b12005226200a74556';


$sn = substr($CW_foaf_name, strrpos($CW_foaf_name, ' ')+1);
$givenname = substr($CW_foaf_name, 0, strpos($CW_foaf_name, ' '));


#http://msdn.microsoft.com/en-us/library/windows/desktop/aa746475(v=vs.85).aspx
#$ldap_query = "(&(sn=*Page*)(givenname=*Anna*))";

$ldap_query = "(&(sn=*$sn*)(givenname=*$givenname*))";

echo "LDAP query: $ldap_query <br>".PHP_EOL;

/*var_dump(
  sha1("mailto:n.d.freear@open.ac.uk"),
  sha1("mailto:N.D.Freear@open.ac.uk"),
  
  sha1(strtolower("mailto:A.C.Page@open.ac.uk"))
);
echo PHP_EOL.'<hr/>'.PHP_EOL;



/*$cn = $name = $displayName = 'N.D.Freear';
$sn = 'Freear';
$givenName = 'Nick';
$sAMAccountName = $mailNickname = '***';
$mail = '***@openmail.open.ac.uk';
$department = 'IET,Institute Services';
$telephoneNumber = '52473';
$physicalDeliveryOfficeName = 'Jennie Lee Building 021';
$title = 'Application Programmer';
$description = 'IET';
*/

#$filter = "(sn=$person*)"; #"(|(cn=$person*)(givenName=$person*))";  #"(sn=$sn)";

endif; //$is_library;


// using ldap bind
#$ldaprdn = 'uname'; // ldap rdn or dn
#$ldappass= 'password';  // associated password


class LdapOU {

  public static $attrs_ou = array('sn','cn','givenName','sAMAccountName','mail',
    'telephoneNumber','department','physicalDeliveryOfficeName','title',
    'description','memberof','wWWHomePage','whenCreated','whenChanged', 'type'); #Not 'ou'.

  private $con, $dn, $data;

  public function __construct($user,$pass, #$ldaprdn
    $host='DC1.open.ac.uk:3268',$dn='DC=Open,DC=AC,DC=UK') { #'OU=IET'   <<<<<<<<(FIXME! Was tungsten, now DC1,DC2.)

    $this->dn = $dn;
    $this->con = ldap_connect($host)
      or die("Could not connect to LDAP server.");
    $this->status();

    $ldapbind = ldap_bind($this->con, $user, $pass);
    $this->status('bind');
  }

  public function __destruct() {
    ldap_unbind($this->con) or die("LDAP unbind: error.");
    echo "LDAP unbind: OK. <br>\n";
  }

  public function search($filter, $attrs=array('cn','sn','mail','sAMAccountName')) {
    /*if (!is_array($arg2) || false!==strpos('(sn=%s*)', '%')) { #$q, $arg2,
      $filter = sprintf($arg2, $q);
    } else {
      $filter = $q;
      $attrs = $arg2;
    }*/

    $sr = ldap_search($this->con, $this->dn, $filter, $attrs);
    $this->status('search');

    $this->data = ldap_get_entries($this->con, $sr);
    $this->status('get_entries');
    return isset($this->data['count']) ? $this->data['count'] : false;
  }

  public function get($name, $idx=0, $j=0, $return=false) {
    $info = $this->data;
    if (!isset($info[$j][strtolower($name)])) return null;

    $value = $info[$j][strtolower($name)];
    unset($value['count']);
    if ('*'!==$idx) { #OR ($idx >= 0)
      $value = $value[$idx];
    }
    if ($return) return $value;

    echo "$name=";
    if (is_array($value)) {
        echo " <pre>";
        var_dump($value);
        echo "</pre>\n";
    } else {
        echo $value," <br>\n";
    }
    return $value;
  }

  public function dump() {
    echo "<pre>\n";
    var_dump($this->data);
    echo "</pre>\n";
  }

  private function status($func='connect') {
    echo "LDAP $func: ",ldap_error($this->con)," <br>\n";
  }

}


if (!$is_library):


$ldap = new LdapOU($ldaprdn, $ldappass);

$count = $ldap->search($ldap_query, LdapOU::$attrs_ou);

#$count = $ldap->search("(sn=$person*)", LdapOU::$attrs_ou);
#$count = $ldap->search("(samaccountname=$person*)", LdapOU::$attrs_ou);
echo "Count results: $count<br>".PHP_EOL;

$ldap->get('SAMAccountName');
$ldap->get('cn');
$ldap->get('sn');
$ldap->get('telephoneNumber');
$ldap->get('department');
#$ldap->get('memberOf', '*');

$ldap->get('mail');

#$ldap->get('wWWHomePage');
$ldap->dump();


$OU_mbox = $ldap->get('cn').'@open.ac.uk';

$OU_mbox_sha1 = sha1(strtolower('mailto:' .$OU_mbox));

if ($CW_foaf_mbox_sha1 == $OU_mbox_sha1) {
  echo ">> OK, the Cloudworks mailbox sha1 matches the OU mailbox sha1. <br>".PHP_EOL;
} else {
  echo ">> NO, the Cloudworks mailbox sha1 does NOT match the OU mailbox sha1. <br>".PHP_EOL;
}




/*
$server = 'tungsten.open.ac.uk:3268';
#$dn = 'CN=mcs-server-agent,OU=Users,OU=MCT,DC=Open,DC=AC,DC=UK'; #"o=My Company, c=US";
$dn = 'DC=Open,DC=AC,DC=UK';  #'OU=IET'

// connect to ldap server
$ldapconn = ldap_connect($server)
    or die("Could not connect to LDAP server.");
myLdapStatus();

#if ($ldapconn) {

    // binding to ldap server
    $ldapbind = ldap_bind($ldapconn, $ldaprdn, $ldappass);

    // verify binding
    //if ($ldapbind) {
myLdapStatus('bind');


$sr = ldap_search($ldapconn, $dn, $filter, $attrs_sched); #, $justthese);
myLdapStatus('search');

$info = ldap_get_entries($ldapconn, $sr);

ldap_unbind($ldapconn) or die('LDAP unbind: error.');
echo "LDAP unbind: OK <br>\n";


echo $info["count"]." entries returned <br>\n";

myLdapGet('sAMAccountName');
myLdapGet('cn');
myLdapGet('telephoneNumber');
myLdapGet('department');
#myLdapGet('memberOf', '*');


echo "<pre>\n";
#var_dump($info);



function myLdapGet($name, $idx=0, $j=0) {
    global $info;
    $value = $info[$idx][strtolower($name)];
    unset($value['count']);
    if ('*'!==$idx) { #OR ($idx >= 0)
      $value = $value[$j];
    }
    echo "$name=";
    if (is_array($value)) {
        echo " <pre>";
        var_dump($value);
        echo "</pre>\n";
    } else {
        echo $value," <br>\n";
    }
    #echo " <br>\n";
    return $value;
}


function myLdapStatus($verb = 'connect') {
    global $ldapconn;
    echo "LDAP $verb: ",ldap_error($ldapconn)," <br>\n";
}*/

endif; //$is_library;
?>