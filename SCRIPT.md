# Обновление до php 8.1 с помощью rector

## Установка

1. Запускаем docker-контейнеры командой `docker-compose up -d`
2. Логинимся в контейнер `php` командой `docker exec -it php sh`
3. Устанавливаем зависимости командой `composer install`
4. Запускаем тесты командой `vendor/bin/codecept run`, видим, что тесты проходят

## Устанавливаем и прогоняем rector

1. Устанавливаем rector командой `composer require rector/rector --dev`
2. Добавляем файл `rector.php`
    ```php
    <?php
    
    use Rector\Config\RectorConfig;
    use Rector\Core\ValueObject\PhpVersion;
    use Rector\Set\ValueObject\LevelSetList;
    use Rector\Symfony\Set\SymfonySetList;
    
    return static function (RectorConfig $rectorConfig): void {
        $rectorConfig->paths([__DIR__ . '/src']);
        $rectorConfig->phpVersion(PhpVersion::PHP_81);
        $rectorConfig->autoloadPaths([__DIR__ . '/migrations']);
        $rectorConfig->symfonyContainerXml(
            __DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.xml',
        );
    
        $rectorConfig->import(LevelSetList::UP_TO_PHP_81);
        $rectorConfig->import(SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES);
    };
    ```
3. Запускаем Rector командой `vendor/bin/rector process --ansi --autoload-file $PWD/vendor/autoload.php`, видим
   применённые изменения.

## Обновляем php до 8.1

1. Исправляем файл `docker\Dockerfile` для использования php 8.1
    ```dockerfile
    FROM php:8.1.15-fpm-alpine
    
    # Install dev dependencies
    RUN apk update \
        && apk upgrade --available \
        && apk add --virtual build-deps \
            autoconf \
            build-base \
            icu-dev \
            libevent-dev \
            openssl-dev \
            zlib-dev \
            libzip \
            libzip-dev \
            zlib \
            zlib-dev \
            bzip2 \
            git \
            libpng \
            libpng-dev \
            libjpeg \
            libjpeg-turbo-dev \
            libwebp-dev \
            libmemcached-dev \
            freetype \
            freetype-dev \
            postgresql-dev \
            curl \
            wget \
            bash \
            rabbitmq-c \
            rabbitmq-c-dev
    
    # Install Composer
    RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer
    
    # Install PHP extensions
    RUN docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/
    RUN docker-php-ext-install -j$(getconf _NPROCESSORS_ONLN) \
        intl \
        gd \
        bcmath \
        pcntl \
        pdo_pgsql \
        sockets \
        zip
    RUN pecl channel-update pecl.php.net \
        && pecl install -o -f \
            redis \
            event \
            memcached \
            amqp \
        && rm -rf /tmp/pear \
        && echo "extension=redis.so" > /usr/local/etc/php/conf.d/redis.ini \
        && echo "extension=event.so" > /usr/local/etc/php/conf.d/event.ini \
        && echo "extension=memcached.so" > /usr/local/etc/php/conf.d/memcached.ini \
        && echo "extension=amqp.so" > /usr/local/etc/php/conf.d/amqp.ini
    ```
2. Пересобираем и запускаем контейнеры командой `docker-compose up -d --build`
3. Запускаем тесты командой `vendor/bin/codecept run`, видим ошибку запуска

## Исправляем ошибки, связанные Enum

1. В файле `tests/functional/UserManagerCest.php` убираем скобки при обращении к значениям enum-классов.
2. Запускаем тесты командой `vendor/bin/codecept run`, видим ошибки, связанные с Doctrine
3. В классе `App\Entity\User` исправляем аннотации на полях `userStatus` и `userType`.
    ```php
    /**
     * @ORM\Column(type="string", length="10", enumType="\App\Type\UserStatus", nullable=false)
     */
    private UserStatus $userStatus;
    
    ...
    
    /**
     * @ORM\Column(type="string", length="10", enumType="\App\Type\UserType", nullable=false)
     */
    private UserType $userType
    ```
4. Ещё раз запускаем тесты командой `vendor/bin/codecept run`, видим ошибки про метод `equals`
5. В классе `App\Manager\UserManager` исправляем методы `deactivateUser` и `changeUserType`
    ```php
    public function deactivateUser(User $user): void
    {
        if ($user->getUserType() === UserType::employee) {
            $user->setUserStatus(UserStatus::suspended);
        } else {
            $user->setUserStatus(UserStatus::disabled);
        }

        $this->entityManager->flush();
    }

    public function changeUserType(User $user, UserType $userType): void
    {
        $user->setUserType($userType);
        if ($userType === UserType::employee && $user->getUserStatus() === UserStatus::disabled) {
            $user->setUserStatus(UserStatus::suspended);
        } elseif ($userType !== UserType::employee && $user->getUserStatus() === UserStatus::suspended) {
            $user->setUserStatus(UserStatus::disabled);
        }

        $this->entityManager->flush();
    }
    ```
6. Ещё раз запускаем тесты командой `vendor/bin/codecept run`, наконец, видим, что тесты проходят

## Добавляем readonly-свойства

1. В классе `App\Entity\User` исправляем сигнатуру конструктора
    ```php
    public function __construct(
    /**
     * @ORM\Column(type="string", length="100", nullable=false)
     */
    private readonly string $firstName,
    /**
     * @ORM\Column(type="string", length="100", nullable=false)
     */
    private readonly string $middleName,
    /**
     * @ORM\Column(type="string", length="100", nullable=false)
     */
    private readonly string $lastName,
    /**
     * @ORM\Column(type="smallint", nullable=false, options={"unsigned"=true})
     */
    private readonly int $age,
    /**
     * @ORM\Column(type="string", length="10", enumType="\App\Type\UserType", nullable=false)
     */
    private UserType $userType)
    ```
2. Запускаем тесты командой `vendor/bin/codecept run`, видимо ошибки, связанные с невозможностью модификации
   readonly-свойств
3. В файле `tests/functional/UserManagerCest.php` исправляем метод `testFindUser` 
4. Запускаем исправленный тест командой `vendor/bin/codecept run tests/functional/UserManagerCest.php::testFindUser`,
   видим, что тест проходит
5. В файле `tests/functional/UserManagerCest.php` исправляем метод `testDeactivateUser` и `testChangeUserType`
    ```php
    /**
     * @dataProvider userTypesDataProvider
     */
    public function testDeactivateUser(FunctionalTester $I, Example $example): void
    {
        $user = new User(
            'Name',
            'Patronymic',
            'Surname',
            34,
            $example['userType'],
        );
        /** @var User $actualUser */
        $userId = $I->haveInRepository($user, ['userStatus' => UserStatus::active]);
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
        $user = new User(
            'Name',
            'Patronymic',
            'Surname',
            34,
            $example['initialUserType'],
        );
        /** @var User $actualUser */
        $userId = $I->haveInRepository($user, ['userStatus' => $example['initialUserStatus']]);
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
    ```
6. Ещё раз запускаем тесты командой `vendor/bin/codecept run`, видим, что тесты проходят
