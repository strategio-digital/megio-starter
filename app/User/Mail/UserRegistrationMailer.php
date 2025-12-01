<?php
declare(strict_types=1);

namespace App\User\Mail;

use App\User\Database\Entity\User;
use Megio\Helper\EnvConvertor;
use Megio\Helper\Path;
use Megio\Http\Resolver\LinkResolver;
use Megio\Mailer\EmailTemplate;
use Megio\Mailer\EmailTemplateFactory;
use Megio\Mailer\SmtpMailer;
use Megio\Translation\Translator;
use Nette\Mail\Message;
use RuntimeException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function substr;

final readonly class UserRegistrationMailer
{
    public function __construct(
        private LinkResolver $linkResolver,
        private EmailTemplateFactory $emailTemplateFactory,
        private Translator $translator,
    ) {}

    public function send(User $user): void
    {
        $token = $user->getActivationToken();

        if ($token === null) {
            throw new RuntimeException($this->translator->translate('user.error.activation_token_missing'));
        }

        $activationLink = $this->linkResolver->link('user.activation', [
            'locale' => substr($this->translator->getLocale(), 0, 2),
            'token' => $token,
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $template = new EmailTemplate(
            file: Path::viewDir() . '/user/mail/user-registration.mail.latte',
            subject: $this->translator->translate('user.mail.registration.subject'),
            params: [
                'activationLink' => $activationLink,
            ],
        );

        $message = new Message()
            ->setSubject($template->getSubject())
            ->setHtmlBody($this->emailTemplateFactory->render($template));

        $message
            ->addTo($user->getEmail())
            ->setFrom(
                email: EnvConvertor::toString($_ENV['SMTP_SENDER']),
                name: EnvConvertor::toString($_ENV['MAIL_SENDER_NAME']),
            )
            ->addBcc(EnvConvertor::toString($_ENV['APP_DEVELOPER_MAIL']));

        $mailer = new SmtpMailer();
        $mailer->send($message);
    }
}
