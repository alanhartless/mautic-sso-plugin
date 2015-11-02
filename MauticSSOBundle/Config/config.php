<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

return array(
    'name'        => 'SSO Providers',
    'description' => 'SSO into Mautic using 3rd party services',
    'version'     => '1.0',
    'author'      => 'Mautic',

    'services'    => array(
        'events' => array(
            'mautic.googleauth.user.subscriber' => array(
                'class' => 'MauticPlugin\MauticSSOBundle\EventListener\UserSubscriber',
                'arguments' => array(
                    'security.password_encoder',
                    'doctrine.orm.entity_manager',
                    'mautic.factory'
                )
            )
        )
    ),
);
