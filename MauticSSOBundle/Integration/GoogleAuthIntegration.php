<?php
/**
 * @package     Mautic
 * @copyright   2015 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticSSOBundle\Integration;

use Mautic\PluginBundle\Integration\AbstractSsoServiceIntegration;
use Mautic\UserBundle\Entity\User;

/**
 * Class GoogleAuthIntegration
 */
class GoogleAuthIntegration extends AbstractSsoServiceIntegration
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'GoogleAuth';
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return 'SSO - Google';
    }

    /**
     * @return string
     */
    public function getAuthenticationType()
    {
        return 'oauth2';
    }

    /**
     * @return string
     */
    public function getAuthScope()
    {
        return 'email profile';
    }

    /**
     * @return string
     */
    public function getAuthenticationUrl()
    {
        return 'https://accounts.google.com/o/oauth2/auth';
    }

    /**
     * @return string
     */
    public function getAccessTokenUrl()
    {
        return 'https://accounts.google.com/o/oauth2/token';
    }

    /**
     * @param mixed $response
     *
     * @return mixed
     */
    public function getUser($response)
    {
        $url = 'https://www.googleapis.com/oauth2/v1/userinfo?alt=json';

        if ($userDetails = $this->makeRequest(
            $url,
            array(),
            'GET',
            array(
                'override_auth_token' => $response['access_token'],
                'append_auth_token' => true
            )
        )
        ) {
            if (isset($userDetails['email'])) {
                $user = new User();
                $user->setUsername($userDetails['email'])
                    ->setEmail($userDetails['email'])
                    ->setFirstName($userDetails['given_name'])
                    ->setLastName($userDetails['family_name'])
                    ->setRole(
                        $this->getUserRole()
                    );

                return $user;
            }
        };

        return false;
    }
}