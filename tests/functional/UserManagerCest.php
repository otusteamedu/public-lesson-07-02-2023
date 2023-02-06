<?php

use App\Entity\User;
use App\Manager\UserManager;
use App\Tests\FunctionalTester;
use App\Type\UserStatus;
use App\Type\UserType;
use Codeception\Example;

class UserManagerCest
{
    public function testSaveUser(FunctionalTester $I): void
    {
        $user = new User(
            'Name',
            'Patronymic',
            'Surname',
            12,
            UserType::student()
        );
        /** @var UserManager $userManager */
        $userManager = $I->grabService(UserManager::class);
        $userId = $userManager->saveUser($user);

        $I->canSeeInRepository(User::class, [
            'id' => $userId,
            'userStatus' => UserStatus::active()
        ]);
    }

    public function testFindUser(FunctionalTester $I): void
    {
        $userParams = [
            'firstName' => 'Name',
            'middleName' => 'Patronymic',
            'lastName' => 'Surname',
            'age' => 34,
            'userType' => UserType::student(),
            'userStatus' => UserStatus::suspended(),
        ];
        /** @var User $actualUser */
        $actualUserId = $I->haveInRepository(User::class, $userParams);
        /** @var UserManager $userManager */
        $userManager = $I->grabService(UserManager::class);

        /** @var User $actualUser */
        $actualUser = $userManager->findUser($actualUserId);

        $I->assertEquals(
            $userParams,
            [
                'firstName' => $actualUser->getFirstName(),
                'middleName' => $actualUser->getMiddleName(),
                'lastName' => $actualUser->getLastName(),
                'age' => $actualUser->getAge(),
                'userType' => $actualUser->getUserType(),
                'userStatus' => $actualUser->getUserStatus(),
            ]
        );
    }

    /**
     * @dataProvider userTypesDataProvider
     */
    public function testDeactivateUser(FunctionalTester $I, Example $example): void
    {
        $userParams = [
            'firstName' => 'Name',
            'middleName' => 'Patronymic',
            'lastName' => 'Surname',
            'age' => 34,
            'userType' => $example['userType'],
            'userStatus' => UserStatus::active(),
        ];
        $userId = $I->haveInRepository(User::class, $userParams);
        /** @var User $user */
        $user = $I->grabEntityFromRepository(User::class, ['id' => $userId]);

        /** @var UserManager $userManager */
        $userManager = $I->grabService(UserManager::class);

        $userManager->deactivateUser($user);

        $I->assertEquals($example['userStatus'], $user->getUserStatus());
    }

    /**
     * @dataProvider userStatusDataProvider
     */
    public function testChangeUserType(FunctionalTester $I, Example $example): void
    {
        $userParams = [
            'firstName' => 'Name',
            'middleName' => 'Patronymic',
            'lastName' => 'Surname',
            'age' => 34,
            'userType' => $example['initialUserType'],
            'userStatus' => $example['initialUserStatus'],
        ];
        $userId = $I->haveInRepository(User::class, $userParams);
        /** @var User $user */
        $user = $I->grabEntityFromRepository(User::class, ['id' => $userId]);

        /** @var UserManager $userManager */
        $userManager = $I->grabService(UserManager::class);

        $userManager->changeUserType($user, $example['targetUserType']);

        $I->assertEquals(
            ['userType' => $example['targetUserType'], 'userStatus' => $example['expectedUserStatus']],
            ['userType' => $user->getUserType(), 'userStatus' => $user->getUserStatus()]
        );
    }

    protected function userTypesDataProvider(): array
    {
        return [
            ['userType' => UserType::student(), 'userStatus' => UserStatus::disabled()],
            ['userType' => UserType::teacher(), 'userStatus' => UserStatus::disabled()],
            ['userType' => UserType::employee(), 'userStatus' => UserStatus::suspended()],
        ];
    }

    protected function userStatusDataProvider(): array
    {
        return [
            ['initialUserType' => UserType::student(), 'initialUserStatus' => UserStatus::active(), 'targetUserType' => UserType::teacher(), 'expectedUserStatus' => UserStatus::active()],
            ['initialUserType' => UserType::student(), 'initialUserStatus' => UserStatus::active(), 'targetUserType' => UserType::employee(), 'expectedUserStatus' => UserStatus::active()],
            ['initialUserType' => UserType::student(), 'initialUserStatus' => UserStatus::disabled(), 'targetUserType' => UserType::teacher(), 'expectedUserStatus' => UserStatus::disabled()],
            ['initialUserType' => UserType::student(), 'initialUserStatus' => UserStatus::disabled(), 'targetUserType' => UserType::employee(), 'expectedUserStatus' => UserStatus::suspended()],
            ['initialUserType' => UserType::teacher(), 'initialUserStatus' => UserStatus::active(), 'targetUserType' => UserType::student(), 'expectedUserStatus' => UserStatus::active()],
            ['initialUserType' => UserType::teacher(), 'initialUserStatus' => UserStatus::active(), 'targetUserType' => UserType::employee(), 'expectedUserStatus' => UserStatus::active()],
            ['initialUserType' => UserType::teacher(), 'initialUserStatus' => UserStatus::disabled(), 'targetUserType' => UserType::student(), 'expectedUserStatus' => UserStatus::disabled()],
            ['initialUserType' => UserType::teacher(), 'initialUserStatus' => UserStatus::disabled(), 'targetUserType' => UserType::employee(), 'expectedUserStatus' => UserStatus::suspended()],
            ['initialUserType' => UserType::employee(), 'initialUserStatus' => UserStatus::active(), 'targetUserType' => UserType::student(), 'expectedUserStatus' => UserStatus::active()],
            ['initialUserType' => UserType::employee(), 'initialUserStatus' => UserStatus::active(), 'targetUserType' => UserType::teacher(), 'expectedUserStatus' => UserStatus::active()],
            ['initialUserType' => UserType::employee(), 'initialUserStatus' => UserStatus::suspended(), 'targetUserType' => UserType::student(), 'expectedUserStatus' => UserStatus::disabled()],
            ['initialUserType' => UserType::employee(), 'initialUserStatus' => UserStatus::suspended(), 'targetUserType' => UserType::teacher(), 'expectedUserStatus' => UserStatus::disabled()],
        ];
    }
}
