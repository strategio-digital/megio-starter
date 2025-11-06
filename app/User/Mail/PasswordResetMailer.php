<?php
declare(strict_types=1);

namespace App\User\Mail;

use App\User\Database\Entity\User;
use Megio\Helper\EnvConvertor;
use Megio\Helper\Path;
use Megio\Http\Resolver\LinkResolver;
use Megio\Mailer\EmailTemplate;
use Megio\Mailer\SmtpMailer;
use Nette\Mail\Message;
use RuntimeException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class PasswordResetMailer
{
    public function __construct(
        private LinkResolver $linkResolver,
    ) {}

    public function send(User $user): void
    {
        $token = $user->getResetPasswordToken();

        if ($token === null) {
            throw new RuntimeException('User reset password token is missing.');
        }

        $resetLink = $this->linkResolver->link('user.reset-password', [
            'token' => $token,
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $template = new EmailTemplate(
            file: Path::viewDir() . '/user/mail/password-reset.mail.latte',
            subject: 'Reset your password',
            params: [
                'resetLink' => $resetLink,
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
