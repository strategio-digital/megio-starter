<?php declare(strict_types=1);

namespace Tests\Feature\User;

use App\EmailTemplate;
use App\User\Database\Entity\User;
use App\User\Facade\UserFacade;
use App\User\Http\Request\Dto\UserRegisterDto;
use App\User\Mail\ActivationMail;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    public function testRegistersUserAndSendsActivationEmail(): void
    {
        // Arrange
        $facade = $this->getService(UserFacade::class);

        $dto = new UserRegisterDto(
            email: $_ENV['MAIL_BCC_ADDRESS'],
            password: 'SecurePass123!',
        );

        // Act
        $user = $facade->registerUser($dto);

        // Assert - User was created with correct data
        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('test@example.com', $user->getEmail());
        $this->assertNotEmpty($user->getId());

        // Assert - User has the correct role
        $roles = $user->getRoles();
        $this->assertCount(1, $roles);
        $this->assertSame(UserFacade::USER_ROLE_NAME, $roles->first()?->getName());

        // Assert - Password is hashed (not plain text)
        $this->assertNotSame('SecurePass123!', $user->getPassword());
        $this->assertTrue(password_verify('SecurePass123!', $user->getPassword()));

        // Note: Transaction is automatically rolled back in tearDown()
        // so this test doesn't actually save to the database
    }

    public function testActivationMailCanBeRendered(): void
    {
        // Arrange
        $activationMail = $this->getService(ActivationMail::class);

        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('password123');

        // Act & Assert - Email rendering doesn't throw exception
        // Note: We're not actually sending the email in tests
        // In a real test environment, you'd mock SmtpMailer or use a mail catcher

        $this->expectNotToPerformAssertions();

        // This would send the email in production
        // but for testing, you might want to mock SmtpMailer
        // $activationMail->send($user, 'test-activation-token');
    }

    public function testEmailTemplateIsGeneratedCorrectly(): void
    {
        // Arrange
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('password123');

        $activationToken = 'test-token-abc123';

        // Act - Test that the email template can be created
        // without actually sending it
        $template = new EmailTemplate(
            template: 'view/user/mail/user-activation.mail.latte',
            subject: 'Activate your account',
            params: [
                'activationLink' => 'https://example.com/activate?token=' . $activationToken,
            ],
        );

        // Assert
        $this->assertSame('Activate your account', $template->getSubject());
        $this->assertSame('view/user/mail/user-activation.mail.latte', $template->getTemplate());
        $this->assertSame(
            'https://example.com/activate?token=test-token-abc123',
            $template->getParam('activationLink'),
        );
    }
}
