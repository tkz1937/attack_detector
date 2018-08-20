<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'attack_detector';
$app['version'] = '2.4.0';
$app['release'] = '1';
$app['vendor'] = 'ClearFoundation';
$app['packager'] = 'ClearFoundation';
$app['license'] = 'GPLv3';
$app['license_core'] = 'LGPLv3';
$app['description'] = lang('attack_detector_app_description');

/////////////////////////////////////////////////////////////////////////////
// App name and categories
/////////////////////////////////////////////////////////////////////////////

$app['name'] = lang('attack_detector_app_name');
$app['category'] = lang('base_category_gateway');
$app['subcategory'] = lang('base_subcategory_intrusion_protection');

/////////////////////////////////////////////////////////////////////////////
// Controllers
/////////////////////////////////////////////////////////////////////////////

$app['controllers']['attack_detector']['title'] = $app['name'];
$app['controllers']['settings']['title'] = lang('base_settings');
$app['controllers']['log']['title'] = lang('attack_detector_log');

/////////////////////////////////////////////////////////////////////////////
// Packaging
/////////////////////////////////////////////////////////////////////////////

// FIXME: remove app-ssh-server dependency
$app['requires'] = array(
    'app-network',
    'app-ssh-server',
);

$app['core_requires'] = array(
    'app-events-core',
    'app-network-core',
    'fail2ban-server',
    'ipset'
);

$app['core_directory_manifest'] = array(
    '/var/clearos/attack_detector' => array(),
    '/var/clearos/attack_detector/filters' => array(),
    '/var/clearos/attack_detector/state' => array(),
    '/var/clearos/attack_detector/run' => array(
        'mode' => '0700'
    ),
);

$app['core_file_manifest'] = array(
    'fail2ban.php'=> array('target' => '/var/clearos/base/daemon/fail2ban.php'),
    'app-attack-detector.sudoers' => array(
        'target' => '/etc/sudoers.d/app-attack-detector',
        'mode' => '0440',
    ),
    '90-attack-detector' => array(
        'target' => '/etc/clearos/firewall.d/90-attack-detector',
        'mode' => '0755',
    ),
);

$app['delete_dependency'] = array(
    'app-attack-detector-core',
    'fail2ban-server',
);
$app['powered_by'] = array(
        'vendor' => array(
        'name' => 'itot',
        'url' => '',
    ),
        'packages' =>  array(
            'attack_detector' => array(
                'name' => 'itot',
               
                'url' => '',
    ), 
    ),
);
