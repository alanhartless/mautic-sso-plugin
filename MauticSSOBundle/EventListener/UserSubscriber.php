<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticSSOBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\PluginBundle\Integration\AbstractSsoIntegration;
use Mautic\UserBundle\Entity\User;
use Mautic\UserBundle\Event\AuthenticationEvent;
use Mautic\UserBundle\UserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserSubscriber
 */
class UserSubscriber implements EventSubscriberInterface
{
    /**
     * @var MauticFactory
     */
    private $factory;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @var EntityManager
     */
    private $em;

    private $supportedServices = array(
        'GithubAuth',
        'GoogleAuth'
    );

    public function __construct(UserPasswordEncoderInterface $encoder, EntityManager $em,  MauticFactory $factory)
    {
        $this->em      = $em;
        $this->factory = $factory;
        $this->encoder = $encoder;
    }

    /**
     * @return array
     */
    static public function getSubscribedEvents()
    {
        return array(
            UserEvents::USER_FORM_AUTHENTICATION => array('onUserFormAuthentication', 0),
            UserEvents::USER_PRE_AUTHENTICATION  => array('onUserAuthentication', 0)
        );
    }

    /**
     * Authenticate via the form using users defined in authorized_users
     *
     * @param AuthenticationEvent $event
     *
     * @return bool|void
     */
    public function onUserFormAuthentication(AuthenticationEvent $event)
    {
        $username = $event->getUsername();
        $password = $event->getToken()->getCredentials();

        $user = new User();
        $user->setUsername($username);

        $authorizedUsers = $this->factory->getParameter('authorized_users');

        if (is_array($authorizedUsers) && isset($authorizedUsers[$username])
    ) {
            $testUser = $authorizedUsers[$username];
            $user->setPassword($testUser['password']);
            if ($this->encoder->isPasswordValid($user, $password)) {
                $user->setFirstName($testUser['firstname'])
                    ->setLastName($testUser['lastname'])
                    ->setEmail($testUser['email'])
                    ->setRole(
                        $this->em->getReference('MauticUserBundle:Role', 1)
                    );
                $event->setIsAuthenticated('authorized_users', $user, true);
            }
        }
    }

    /**
     * @param AuthenticationEvent $event
     */
    public function onUserAuthentication(AuthenticationEvent $event)
    {
        $result = false;
        if ($authenticatingService = $event->getAuthenticatingService()) {
            if (in_array($authenticatingService, $this->supportedServices) && $integration = $event->getIntegration($authenticatingService)) {
                $result = $this->authenticateService($integration, $event->isLoginCheck());
            }
        }

        if ($result instanceof User) {
            $event->setIsAuthenticated($authenticatingService, $result, $integration->shouldAutoCreateNewUser());
        } elseif ($result instanceof Response) {
            $event->setResponse($result);
        } // else do nothing
    }

    /**
     * @param AbstractSsoIntegration $integration
     * @param                        $loginCheck
     *
     * @return bool|RedirectResponse
     */
    private function authenticateService(AbstractSsoIntegration $integration, $loginCheck)
    {
        if ($loginCheck) {
            if ($authenticatedUser = $integration->ssoAuthCallback()) {

                return $authenticatedUser;
            }
        } else {
            $loginUrl = $integration->getAuthLoginUrl();
            $response = new RedirectResponse($loginUrl);

            return $response;
        }

        return false;
    }
}
