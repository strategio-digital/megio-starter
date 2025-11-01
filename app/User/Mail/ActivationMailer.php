<?php
declare(strict_types=1);

namespace App\User\Mail;

use App\App\EnvReader\EnvConvertor;
use App\App\Mail\EmailTemplate;
use App\User\Database\Entity\User;
use Megio\Helper\Path;
use Megio\Http\Resolver\LinkResolver;
use Megio\Mailer\SmtpMailer;
use Nette\Mail\Message;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class ActivationMailer
{
    public function __construct(
        private LinkResolver $linkResolver,
    ) {}

    public function send(
        User $user,
        string $activationToken,
    ): void {
        $activationLink = $this->linkResolver->link('user.activation', [
            'uuid' => $user->getId(),
            'token' => $activationToken,
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $template = new EmailTemplate(
            file: Path::viewDir() . '/user/mail/user-activation.mail.latte',
            subject: 'Activate your account',
            params: [
                'activationLink' => $activationLink,
            ],
        );

        $message = new Message();
        $message
            ->setSubject($template->getSubject())
            ->setHtmlBody($template->render());

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
