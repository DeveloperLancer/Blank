<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki <kubuspl@onet.eu>
 * @copyright
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class IndexController extends AbstractController
{
    #[Route(path: '/', name: 'app_index')]
    public function index(Request $request): Response
    {

        return $this->render('index.html.twig', ['locale' => $request->getLocale()]);
    }


    #[Route(path: '/auth', name: 'app_auth')]
    public function auth(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        $user = ($this->getUser())? $this->getUser()->getUserIdentifier() : 'non-user';
        return $this->json([$error,$user,$request->headers->all()]);
    }
}