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
 * Class GithubAuthIntegration
 */
class GithubAuthIntegration extends AbstractSsoServiceIntegration
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'GithubAuth';
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return 'SSO - Github';
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
        return 'user:email';
    }

    /**
     * @return string
     */
    public function getAuthenticationUrl()
    {
        return 'https://github.com/login/oauth/authorize';
    }

    /**
     * @return string
     */
    public function getAccessTokenUrl()
    {
        return 'https://github.com/login/oauth/access_token';
    }

    /**
     * @param mixed $response
     *
     * @return mixed
     */
    public function getUser($response)
    {
        if ($userDetails = $this->makeRequest(
            'https://api.github.com/user',
            array(),
            'GET',
            array(
                'override_auth_token' => $response['access_token'],
                'append_auth_token' => true
            )
        )
        ) {
            if (isset($userDetails['login'])) {
                $names = explode(' ', $userDetails['name']);
                if (count($names) > 1) {
                    $firstname = $names[0];
                    unset($names[0]);
                    $lastname = implode(' ', $names);
                } else {
                    $firstname = $lastname = $names[0];
                }

                // Get email
                $emails = $this->makeRequest(
                    'https://api.github.com/user/emails',
                    array(),
                    'GET',
                    array(
                        'override_auth_token' => $response['access_token'],
                        'append_auth_token' => true
                    )
                );

                if (is_array($emails) && count($emails)) {
                    foreach ($emails as $ghEmail) {
                        if ($ghEmail['primary']) {
                            $email = $ghEmail['email'];

                            break;
                        }
                    }
                }

                if (empty($email)) {

                    // Email could not be found so bail
                    return false;
                }

                $user = new User();
                $user->setUsername($userDetails['login'])
                    ->setEmail($email)
                    ->setFirstName($firstname)
                    ->setLastName($lastname)
                    ->setRole(
                        $this->getUserRole()
                    );

                return $user;
            }
        };

        return false;
    }
}