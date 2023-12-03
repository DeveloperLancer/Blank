<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki <kubuspl@onet.eu>
 * @copyright
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security;

use Scheb\TwoFactorBundle\Security\Http\Authentication\AuthenticationRequiredHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class TwoFactorAuthenticationHandler implements AuthenticationFailureHandlerInterface, AuthenticationRequiredHandlerInterface, AuthenticationSuccessHandlerInterface
{

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        // Return the response to tell the client that 2fa failed. You may want to add more details
        // from the $exception.
        return new Response('{"error": "2fa_failed", "two_factor_complete": false}');
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationRequired(Request $request, TokenInterface $token): Response
    {
        // Return the response to tell the client that authentication hasn't completed yet and
        // two-factor authentication is required.
        return new Response('{"error": "access_denied", "two_factor_complete": false}');
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        // Return the response to tell the client that authentication including two-factor
        // authentication is complete now.
        return new Response('{"login": "success", "two_factor_complete": true}');
    }
}