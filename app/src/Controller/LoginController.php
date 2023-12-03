<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\DiscordAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;

use Scheb\TwoFactorBundle\Security\TwoFactor\TwoFactorFirewallContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private TwoFactorFirewallContext $twoFactorFirewallContext,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
    )
    {
    }

    #[Route(path: '/auth/discord', name: 'auth_discord')]
    public function loginDiscord(Request $request)
    {
        if (!$request->getSession()->has(DiscordAuthenticator::DISCORD_ID))
            return $this->redirectToRoute('app_index'); //todo error bo w teorii powinno byc to id, czyli ktos tu wszedl np z linku

        $discordId = $request->getSession()->get(DiscordAuthenticator::DISCORD_ID);

        /** @var null|User $user */
        $user = $this->getUser();

        //Laczenie konta z discordem gdy ktos jest zalogowany
        if ($user) {
            //Oznacza to, ze user ma juz polaczone konto z discord
            if ($user->getDiscordId())
                return $this->redirectToRoute('app_index');

            if ($this->userRepository->findOneBy([DiscordAuthenticator::PROPERTY_NAME => $discordId]))
                return $this->redirectToRoute('app_index'); //todo error konto discord jest juz z kims polaczone

            $user->setDiscordId($discordId);
            $this->entityManager->flush();

            $request->getSession()->remove(DiscordAuthenticator::DISCORD_ID);
            //todo udalo sie polaczyc konto z discordem
            return $this->redirectToRoute('app_index');
        }

        //todo formularz tworzenia uzytkownika
        //todo tworzenie uzytkownika
        return $this->json($discordId);
    }

    #[Route(path: '/login/discord', name: 'auth_discord_start')]
    public function startDiscord(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry->getClient("discord")->redirect(["identify"]);
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED_2FA_IN_PROGRESS')) {
            $token = $this->tokenStorage->getToken();
            $config = $this->twoFactorFirewallContext->getFirewallConfig($token->getFirewallName());
            $pendingTwoFactorProviders = $token->getTwoFactorProviders();
            $preferredProvider = $request->query->get('preferProvider');

            if ($preferredProvider)
                $token->preferTwoFactorProvider((string) $preferredProvider);
            $checkPath = $config->getCheckPath();
            $isRoute = !str_contains($checkPath, '/');

            $params = [
                'twoFactorProvider' => $token->getCurrentTwoFactorProvider(),
                'availableTwoFactorProviders' => $pendingTwoFactorProviders,

//                'authenticationError' => $authenticationException ? $authenticationException->getMessageKey() : null,
//                'authenticationErrorData' => $authenticationException ? $authenticationException->getMessageData() : null,
//                'displayTrustedOption' => $displayTrustedOption,
                'authCodeParameterName' => $config->getAuthCodeParameterName(),
                'trustedParameterName' => $config->getTrustedParameterName(),
                'isCsrfProtectionEnabled' => $config->isCsrfProtectionEnabled(),
                'csrfParameterName' => $config->getCsrfParameterName(),
                'csrfTokenId' => $config->getCsrfTokenId(),
                'checkPathRoute' => $isRoute ? $checkPath : null,
                'checkPathUrl' => $isRoute ? null : $checkPath,
//                'logoutPath' => $this->logoutUrlGenerator->getLogoutPath(),
            ];

            return $this->render('security/oldlogin.html.twig', $params);

        }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/oldlogin.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

//    #[Route(path: '/login', name: 'app_login')]
//    public function login(AuthenticationUtils $authenticationUtils): Response
//    {
//        // get the login error if there is one
//        $error = $authenticationUtils->getLastAuthenticationError();
//
//        // last username entered by the user
//        $lastUsername = $authenticationUtils->getLastUsername();
//
//        return $this->render('security/login.html.twig', [
//            'last_username' => $lastUsername,
//            'error' => $error,
//        ]);
//    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
