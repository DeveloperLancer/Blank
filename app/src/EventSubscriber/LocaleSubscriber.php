<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki <kubuspl@onet.eu>
 * @copyright
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LocaleSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            // must be registered before (i.e. with a higher priority than) the default Locale listener
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
            LoginSuccessEvent::class => [['onSuccessfulLogin', 20]],
        ];
    }

    public function onSuccessfulLogin(LoginSuccessEvent $event)
    {
        /** @var User $user */
        $user = $event->getUser();
        $request = $event->getRequest();
        $request->getSession()->set('_locale', $user->getLocale());
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $preferred = $request->getPreferredLanguage($request->getLanguages());

        if (!$request->hasPreviousSession()) {
            $request->setLocale($preferred);
            return;
        }

        // try to see if the locale has been set as a _locale routing parameter
        if ($locale = $request->attributes->get('_locale')) {
            $request->getSession()->set('_locale', $locale);
        } else {
            if ($request->getSession()->has('_locale')) {
                $request->setLocale($request->getSession()->get('_locale'));
                return;
            }

            $request->setLocale($preferred);
        }
    }
}