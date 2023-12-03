<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki <kubuspl@onet.eu>
 * @copyright
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserMailerSubscriber implements EventSubscriberInterface
{

    private string $sender;
    private string $name;

    public function __construct(
        private MailerInterface $mailer,
        private ContainerBagInterface $container,
        private TranslatorInterface $translator
    )
    {
        $this->sender = $this->container->get('app.email.notification.sender');
        $this->name = $this->container->get('app.email.notification.name');
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => [['onSuccessfulLogin', 0]],
            //LoginFailureEvent::class => [],
        ];
    }

    public function onSuccessfulLogin(LoginSuccessEvent $event): void
    {
        /** @var User $user */
        $user = $event->getUser();

        try {
            $this->mailer->send((new TemplatedEmail())
                ->from(new Address($this->sender, $this->name))
                ->to($user->getEmail())
                ->subject($this->translator->trans('Notification about successful login', [], null, $user->getLocale())) //todo message
                ->htmlTemplate('email/login_success.html.twig')
            );
        } catch (TransportExceptionInterface) {}
    }
}