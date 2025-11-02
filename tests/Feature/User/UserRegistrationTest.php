<?php declare(strict_types=1);

namespace Tests\Feature\User;

use App\App\EnvReader\EnvConvertor;
use App\App\Mail\EmailTemplate;
use App\User\Database\Entity\User;
use App\User\Facade\Exception\UserAuthFacadeException;
use App\User\Facade\UserAuthFacade;
use App\User\Http\Request\Dto\UserRegisterDto;
use Doctrine\ORM\Exception\ORMException;
use Megio\Database\Entity\EntityException;
use Megio\Helper\Path;
use Tests\TestCase;

use function password_verify;

class UserRegistrationTest extends TestCase
{
    /**
     * @throws UserAuthFacadeException
     * @throws EntityException
     * @throws ORMException
     */
    public function testRegistersUser(): void
    {
        // Arrange
        $facade = $this->getService(UserAuthFacade::class);
        $developerMail = EnvConvertor::toString($_ENV['APP_DEVELOPER_MAIL']);

        $userRegisterDto = new UserRegisterDto(
            email: $developerMail,
            password: 'SecurePass123!',
        );

        // Act
        $user = $facade->registerUser($userRegisterDto);

        // Assert - User was created with correct data
        $this->assertInstanceOf(User::class, $user);
        $this->assertSame($developerMail, $user->getEmail());
        $this->assertNotEmpty($user->getId());

        // Assert - User has the correct role
        $roles = $user->getRoles();
        $firstRole = $roles->first();

        $this->assertCount(1, $roles);
        $this->assertNotFalse($firstRole, 'User should have at least one role');
        $this->assertSame(UserAuthFacade::USER_ROLE_NAME, $firstRole->getName());

        // Assert - Password is hashed (not plain text)
        $this->assertNotSame('SecurePass123!', $user->getPassword());
        $this->assertTrue(password_verify('SecurePass123!', $user->getPassword()));

        // Note: Transaction is automatically rolled back in tearDown()
        // so this test doesn't actually save to the database
    }

    public function testEmailTemplateSupportsAbsolutePaths(): void
    {
        // Arrange
        $activationToken = 'test-token-xyz789';

        // Act - Create template with absolute path using Path::viewDir()
        $templateWithAbsolutePath = new EmailTemplate(
            file: Path::viewDir() . '/user/mail/user-registration.mail.latte',
            subject: 'Activate your account',
            params: [
                'activationLink' => 'https://example.com/activate?token=' . $activationToken,
            ],
        );

        // Create template with relative path (existing behavior)
        $templateWithRelativePath = new EmailTemplate(
            file: 'view/user/mail/user-registration.mail.latte',
            subject: 'Activate your account',
            params: [
                'activationLink' => 'https://example.com/activate?token=' . $activationToken,
            ],
        );

        // Assert - Both render successfully and produce same output
        $absolutePathHtml = $templateWithAbsolutePath->render();
        $relativePathHtml = $templateWithRelativePath->render();

        $this->assertNotEmpty($absolutePathHtml);
        $this->assertNotEmpty($relativePathHtml);
        $this->assertSame($absolutePathHtml, $relativePathHtml);
    }
}
