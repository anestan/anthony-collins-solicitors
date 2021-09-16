<?php
// Register google api key for ACF google maps field
function acs_acf_google_map_api( $api ){
    $api['key'] = 'AIzaSyBy5UazD5tDLu1zwuf7AW2AEv41FsOqThk';
    return $api;
}
add_filter('acf/fields/google_map/api', 'acs_acf_google_map_api');