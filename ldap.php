<?php

require_once __DIR__ . "/config.php";

function get_ldap_connection() {
    $ldap = ldap_connect(AD_HOST);
    ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

    return $ldap;
}

function ldap_login($username, $password) {
    $ldap = get_ldap_connection();
    
    $ldaprdn = "$username@" . AD_BASEDN;

    $bind = ldap_bind($ldap, $ldaprdn, $password);

    if ($bind) {
        $filter="(sAMAccountName=$username)";
        $result = ldap_search($ldap, "DC=team8,DC=isucdc,DC=com", $filter);
        ldap_sort($ldap, $result, "sn");
        $info = ldap_get_entries($ldap, $result);
        if ($info['count'] != 1) {
            header("HTTP/1.0 401 Unauthorized)");
        }

        $info = $info[0];


        $name = $info["displayname"][0];
        
        $role = get_roles_from_info($info);

        $data = array("username" => $username, "display" => $name, "role" => $role);
    
        @ldap_close($ldap);
        return $data;
    } else {
        header("HTTP/1.0 401 Unauthorized");
    }
}

function get_roles($username) {
    $ldap = get_ldap_connection();

    $ldaprdn = AD_LOOKUP . "@" . AD_BASEDN;

    $bind = @ldap_bind($ldap, $ldaprdn, AD_LOOKUP_PASS);

    if ($bind) {
        $filter="(sAMAccountName=$username)";
        $result = ldap_search($ldap, "DC=team8,DC=isucdc,DC=com", $filter);
        ldap_sort($ldap, $result, "sn");
        $info = ldap_get_entries($ldap, $result);
        if ($info['count'] != 1) {
            return false;
        }
        $info = $info[0];

        $roles = get_roles_from_info($info);

        @ldap_close($ldap);
        return $roles;
    } else {
        return false;
    }
}

function get_roles_from_info($info) {
    $role = array();

    foreach ($info["memberof"] as $grp) {
        if ($grp == "CN=Negligence,CN=Users,DC=team8,DC=isucdc,DC=com")
            $role[] = "Negligence";
        if ($grp == "CN=Divorce,CN=Users,DC=team8,DC=isucdc,DC=com")
            $role[] = "Divorce";
        if ($grp == "CN=TaxEvasion,CN=Users,DC=team8,DC=isucdc,DC=com")
            $role[] = "TaxEvasion";
        if ($grp == "CN=ITTeam,CN=Users,DC=team8,DC=isucdc,DC=com")
            $role[] = "ITTeam";
        if ($grp == "CN=Lawyers,CN=Users,DC=team8,DC=isucdc,DC=com")
            $role[] = "Lawyer";
    }

    return $role;
}