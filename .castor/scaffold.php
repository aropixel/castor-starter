<?php

declare(strict_types=1);

function customizeDockerStarterCastorFile(string $projectDir, string $projectName, string $domain, string $phpVersion): void
{
    $file = $projectDir . '/castor.php';

    if (!file_exists($file)) {
        throw new RuntimeException('Impossible de trouver le castor.php du docker-starter.');
    }

    $content = file_get_contents($file);

    if (false === $content) {
        throw new RuntimeException('Lecture impossible du castor.php.');
    }

    $replacement = <<<PHP
\$projectName = '%s';
\$tld = '%s';

return [
    'project_name' => \$projectName,
    'root_domain' => '%s',
    'extra_domains' => [
        'www.%s',
    ],
    'mail_domain' => 'mail.%s',
    'php_version' => %s,
];
PHP;

    $domainParts = explode('.', $domain, 2);
    $tld = $domainParts[1] ?? 'test';

    $replacement = sprintf(
        $replacement,
        addslashes($projectName),
        addslashes($tld),
        addslashes($domain),
        addslashes($domain),
        addslashes($domain),
        $phpVersion
    );

    $content = preg_replace(
        '/function create_default_variables\(\): array\s*\{.*?return\s+\[.*?\];\s*\}/s',
        "function create_default_variables(): array\n{\n    " . $replacement . "\n}",
        $content
    );

    file_put_contents($file, $content);
}

function createProjectDirectories(string $projectDir): void
{
    $dirs = [
        $projectDir . '/.claude',
        $projectDir . '/application/private',
        $projectDir . '/application/public',
        $projectDir . '/application/public/media',
        $projectDir . '/application/src/Controller/Admin',
        $projectDir . '/application/src/Entity',
        $projectDir . '/application/src/Repository',
        $projectDir . '/application/src/Form/Type',
        $projectDir . '/application/src/Menu',
        $projectDir . '/application/src/DataFixtures',
        $projectDir . '/application/templates/admin/project',
        $projectDir . '/application/templates/bundles/AropixelPageBundle/contact',
        $projectDir . '/application/tests/Functional/Security',
        $projectDir . '/application/tests/Functional/Blog',
        $projectDir . '/application/tests/Functional/Page',
        $projectDir . '/application/tests/Functional/Menu',
        $projectDir . '/application/tests/Functional/Project',
    ];

    foreach ($dirs as $dir) {
        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new RuntimeException(sprintf('Impossible de créer le dossier "%s".', $dir));
        }
    }
}

function copyInitialInfrastructureFiles(string $projectDir): void
{
    $root = dirname(__DIR__);

    $files = [
        $root . '/resources/infrastructure/docker/docker-compose.yml'
        => $projectDir . '/infrastructure/docker/docker-compose.yml',
        $root . '/resources/infrastructure/docker/services/varnish/default.vcl'
        => $projectDir . '/infrastructure/docker/services/varnish/default.vcl',
    ];

    foreach ($files as $source => $target) {
        if (!file_exists($source)) {
            throw new RuntimeException(sprintf('Fichier source manquant : %s', $source));
        }

        $targetDir = dirname($target);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        copy($source, $target);
    }
}

function copyStarterFiles(string $projectDir, string $adminSlug): void
{
    $root = dirname(__DIR__);

    $files = [
        'application/config/packages/security.yaml',
        'application/config/packages/liip_imagine.yaml',
        'application/config/routes/liip_imagine.yaml',
        'application/private/pixel.png',
    ];

    foreach ($files as $file) {
        $source = $root . '/resources/' . $file;
        if (!file_exists($source)) {
            throw new RuntimeException(sprintf('Fichier source manquant : %s', $source));
        }

        $target = $projectDir . '/' . $file;
        $targetDir = dirname($target);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        copy($source, $target);
    }

    // Exemple de remplacement simple dans security.yaml ou autre config
    $adminSluggedFiles = [
        'application/config/packages/security.yaml',
        'application/config/routes/aropixel_admin.yaml',
    ];

    foreach ($adminSluggedFiles as $adminSluggedFile) {
        $source = $root . '/resources/' . $adminSluggedFile;
        if (!file_exists($source)) {
            throw new RuntimeException(sprintf('Fichier source manquant : %s', $source));
        }

        $target = $projectDir . '/' . $adminSluggedFile;
        $targetDir = dirname($target);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $content = file_get_contents($source);
        $content = str_replace('__ADMIN_SLUG__', $adminSlug, $content);
        file_put_contents($target, $content);
    }

    // Copie du dossier clevercloud
    $clevercloudSource = $root . '/resources/clevercloud';
    if (is_dir($clevercloudSource)) {
        $clevercloudTarget = $projectDir . '/clevercloud';
        \Castor\run(sprintf('cp -r %s %s', escapeshellarg($clevercloudSource), escapeshellarg($clevercloudTarget)));
    }
}

function copyAdminBundleSkills(string $projectDir): void
{
    $source = $projectDir . '/application/vendor/aropixel/admin-bundle/skills';

    if (!is_dir($source)) {
        throw new RuntimeException(sprintf('Dossier skills introuvable : %s', $source));
    }

    $target = $projectDir . '/.claude/skills';

    \Castor\run(sprintf('cp -r %s %s', escapeshellarg($source), escapeshellarg($target)));
}

function generateGitignore(string $projectDir): void
{
    $gitignore = $projectDir . '/application/private/.gitignore';
    $content = <<<GITIGNORE
    *
    !pixel.png
GITIGNORE;
    file_put_contents($gitignore, $content);

    $gitignore = $projectDir . '/application/public/media/.gitignore';
    $content = <<<GITIGNORE
    *
    !.gitignore
GITIGNORE;
    file_put_contents($gitignore, $content);
}

function addAdminBundleImportmap(string $projectDir): void
{
    $importmapFile = $projectDir . '/application/importmap.php';

    if (!file_exists($importmapFile)) {
        return;
    }

    $content = file_get_contents($importmapFile);

    if (str_contains($content, '@aropixel/admin-bundle')) {
        return;
    }

    $entry = <<<'PHP'
    '@aropixel/admin-bundle' => [
        'path' => './vendor/aropixel/admin-bundle/assets/admin.js',
        'entrypoint' => true,
    ],
PHP;

    $content = preg_replace('/(\n\];)/', "\n$entry$1", $content);

    file_put_contents($importmapFile, $content);
}

function addMissingBundles(string $projectDir): void
{
    $bundlesFile = $projectDir . '/application/config/bundles.php';

    if (!file_exists($bundlesFile)) {
        throw new RuntimeException(sprintf('Le fichier "%s" est introuvable.', $bundlesFile));
    }

    $content = file_get_contents($bundlesFile);

    $missingBundles = [
        'Liip\ImagineBundle\LiipImagineBundle' => "['all' => true]",
        'Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle' => "['all' => true]",
    ];

    foreach ($missingBundles as $bundleClass => $envs) {
        if (strpos($content, $bundleClass . '::class') === false) {
            $newLine = "    $bundleClass::class => $envs,";
            $content = preg_replace('/(\n\];)/', "\n$newLine$1", $content);
        }
    }

    file_put_contents($bundlesFile, $content);
}

function cleanNginxConfig(string $projectDir): void
{
    $nginxFile = $projectDir . '/infrastructure/docker/services/php/frontend/etc/nginx/nginx.conf';

    if (!file_exists($nginxFile)) {
        // Il se peut que le fichier n'existe pas encore ou que le starter ait une structure différente
        return;
    }

    $content = file_get_contents($nginxFile);

    // Suppression du bloc exact via une expression régulière souple pour gérer les indentations
    $pattern = '/\s*location\s+~\*\s+\\\.\(jpg\|jpeg\|png\|gif\|ico\|css\|js\|svg\)\$\s*\{\s*access_log\s+off;\s*add_header\s+Cache-Control\s+"no-cache";\s*\}/s';
    $content = preg_replace($pattern, '', (string) $content);

    file_put_contents($nginxFile, $content);
}

function customizeDockerfile(string $projectDir): void
{
    $dockerfile = $projectDir . '/infrastructure/docker/services/php/Dockerfile';

    if (!file_exists($dockerfile)) {
        throw new RuntimeException('Impossible de trouver le Dockerfile du docker-starter.');
    }

    $content = file_get_contents($dockerfile);

    if (false === $content) {
        throw new RuntimeException('Lecture impossible du Dockerfile.');
    }

    $pattern = '/"php\$\{PHP_VERSION\}-apcu"\s+\\\\\s+'
        . '"php\$\{PHP_VERSION\}-bcmath"\s+\\\\\s+'
        . '"php\$\{PHP_VERSION\}-cli"\s+\\\\\s+'
        . '"php\$\{PHP_VERSION\}-common"\s+\\\\\s+'
        . '"php\$\{PHP_VERSION\}-curl"\s+\\\\\s+'
        . '"php\$\{PHP_VERSION\}-iconv"\s+\\\\\s+'
        . '"php\$\{PHP_VERSION\}-intl"\s+\\\\\s+'
        . '"php\$\{PHP_VERSION\}-mbstring"\s+\\\\\s+'
        . '"php\$\{PHP_VERSION\}-pgsql"\s+\\\\\s+'
        . '"php\$\{PHP_VERSION\}-uuid"\s+\\\\\s+'
        . '"php\$\{PHP_VERSION\}-xml"\s+\\\\\s+'
        . '"php\$\{PHP_VERSION\}-zip"/';

    $newModules = '"php${PHP_VERSION}-apcu" \\' . "\n"
        . '        "php${PHP_VERSION}-bcmath" \\' . "\n"
        . '        "php${PHP_VERSION}-cli" \\' . "\n"
        . '        "php${PHP_VERSION}-common" \\' . "\n"
        . '        "php${PHP_VERSION}-curl" \\' . "\n"
        . '        "php${PHP_VERSION}-gd" \\' . "\n"
        . '        "php${PHP_VERSION}-iconv" \\' . "\n"
        . '        "php${PHP_VERSION}-imagick" \\' . "\n"
        . '        "php${PHP_VERSION}-intl" \\' . "\n"
        . '        "php${PHP_VERSION}-mbstring" \\' . "\n"
        . '        "php${PHP_VERSION}-mysql" \\' . "\n"
        . '        "php${PHP_VERSION}-pgsql" \\' . "\n"
        . '        "php${PHP_VERSION}-redis" \\' . "\n"
        . '        "php${PHP_VERSION}-uuid" \\' . "\n"
        . '        "php${PHP_VERSION}-xml" \\' . "\n"
        . '        "php${PHP_VERSION}-zip"';

    $content = preg_replace($pattern, $newModules, $content);

    file_put_contents($dockerfile, $content);
}

function customizeDockerPhp(string $projectDir): void
{
    $file = $projectDir . '/.castor/docker.php';

    if (!file_exists($file)) {
        return;
    }

    $content = file_get_contents($file);

    if (false === $content) {
        return;
    }

    $pattern = '/\'PROJECT_NAME\'\s+=>\s+\$c\[\'project_name\'\],\s+'
        . '\'PROJECT_ROOT_DOMAIN\'\s+=>\s+\$c\[\'root_domain\'\],\s+'
        . '\'PROJECT_DOMAINS\'\s+=>\s+\$domains,\s+'
        . '\'USER_ID\'\s+=>\s+\$c\[\'user_id\'\],\s+'
        . '\'PHP_VERSION\'\s+=>\s+\$c\[\'php_version\'\],\s+'
        . '\'REGISTRY\'\s+=>\s+\$c\[\'registry\'\]\s+\?\?\s+\'\',/';

    $replacement = "'PROJECT_NAME' => \$c['project_name'],\n"
        . "    'PROJECT_ROOT_DOMAIN' => \$c['root_domain'],\n"
        . "    'PROJECT_DOMAINS' => \$domains,\n"
        . "    'MAIL_DOMAIN' => \$c['mail_domain'],\n"
        . "    'USER_ID' => \$c['user_id'],\n"
        . "    'PHP_VERSION' => \$c['php_version'],\n"
        . "    'REGISTRY' => \$c['registry'] ?? '',";

    $content = preg_replace($pattern, $replacement, $content);

    file_put_contents($file, $content);
}

function copyContribAllFiles(string $contribDir): void
{
    $root = dirname(__DIR__);

    $files = [
        'application/config/packages/aropixel_menu.yaml',
        'application/config/packages/dev/doctrine_fixtures.yaml',
        'application/src/Entity/Project.php',
        'application/src/Entity/ProjectImage.php',
        'application/src/Repository/ProjectRepository.php',
        'application/src/Form/ProjectType.php',
        'application/src/Form/Type/ContactPageType.php',
        'application/src/Menu/ProjectMenuSource.php',
        'application/src/DataFixtures/ProjectFixture.php',
        'application/src/Controller/Admin/ProjectController.php',
        'application/templates/admin/project/index.html.twig',
        'application/templates/admin/project/form.html.twig',
        'application/templates/admin/project/_actions.html.twig',
        'application/templates/bundles/AropixelPageBundle/contact/form.html.twig',
        'application/tests/Functional/WebTestCase.php',
        'application/tests/Functional/Security/LoginTest.php',
        'application/tests/Functional/Security/AccessControlTest.php',
        'application/tests/Functional/Blog/PostTest.php',
        'application/tests/Functional/Blog/PostCategoryTest.php',
        'application/tests/Functional/Page/PageTest.php',
        'application/tests/Functional/Page/PageBuilderTest.php',
        'application/tests/Functional/Menu/MenuTest.php',
        'application/tests/Functional/Project/ProjectTest.php',
    ];

    foreach ($files as $file) {
        $source = $root . '/resources/' . $file;
        if (!file_exists($source)) {
            throw new RuntimeException(sprintf('Fichier source manquant : %s', $source));
        }

        $target = $contribDir . '/' . $file;
        $targetDir = dirname($target);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        copy($source, $target);
    }
}

function addFixturesBundle(string $contribDir): void
{
    $bundlesFile = $contribDir . '/application/config/bundles.php';

    if (!file_exists($bundlesFile)) {
        throw new RuntimeException(sprintf('Le fichier "%s" est introuvable.', $bundlesFile));
    }

    $content = file_get_contents($bundlesFile);
    $bundleClass = 'Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle';

    if (strpos($content, $bundleClass . '::class') === false) {
        $newLine = "    $bundleClass::class => ['dev' => true, 'test' => true],";
        $content = preg_replace('/(\n\];)/', "\n$newLine$1", $content);
        file_put_contents($bundlesFile, $content);
    }
}

function requireTestDependencies(string $contribDir): void
{
    \Castor\run(
        'castor builder composer require --dev doctrine/doctrine-fixtures-bundle:^4.0 symfony/browser-kit:^7.0 symfony/css-selector:^7.0 dama/doctrine-test-bundle:^8.0',
        context: \Castor\context()->withWorkingDirectory($contribDir)
    );
}

function configureAutoloadDevBundleTests(string $contribDir): void
{
    $composerJson = $contribDir . '/application/composer.json';

    if (!file_exists($composerJson)) {
        throw new RuntimeException(sprintf('Fichier introuvable : %s', $composerJson));
    }

    $json = json_decode((string) file_get_contents($composerJson), true);

    if (!\is_array($json)) {
        throw new RuntimeException('Le fichier composer.json est invalide.');
    }

    $bundleNamespaces = [
        'Aropixel\\AdminBundle\\Tests\\' => '../admin-bundle/tests/',
        'Aropixel\\BlogBundle\\Tests\\' => '../blog-bundle/tests/',
        'Aropixel\\PageBundle\\Tests\\' => '../page-bundle/tests/',
        'Aropixel\\MenuBundle\\Tests\\' => '../menu-bundle/tests/',
    ];

    foreach ($bundleNamespaces as $ns => $path) {
        $json['autoload-dev']['psr-4'][$ns] = $path;
    }

    file_put_contents(
        $composerJson,
        json_encode($json, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES) . "\n"
    );

    \Castor\run(
        'castor builder composer dump-autoload',
        context: \Castor\context()->withWorkingDirectory($contribDir)
    );
}

function configurePhpUnitTestSuites(string $contribDir): void
{
    $phpunitFile = $contribDir . '/application/phpunit.dist.xml';

    if (!file_exists($phpunitFile)) {
        return;
    }

    $content = (string) file_get_contents($phpunitFile);

    if (!str_contains($content, 'AdminBundle')) {
        $bundleSuites = <<<XML

        <testsuite name="AdminBundle">
            <directory>../admin-bundle/tests/Unit</directory>
            <directory>../admin-bundle/tests/Integration</directory>
        </testsuite>
        <testsuite name="BlogBundle">
            <directory>../blog-bundle/tests/Unit</directory>
            <directory>../blog-bundle/tests/Integration</directory>
        </testsuite>
        <testsuite name="PageBundle">
            <directory>../page-bundle/tests/Unit</directory>
            <directory>../page-bundle/tests/Integration</directory>
        </testsuite>
        <testsuite name="MenuBundle">
            <directory>../menu-bundle/tests/Unit</directory>
            <directory>../menu-bundle/tests/Integration</directory>
        </testsuite>
XML;
        $content = str_replace('</testsuites>', $bundleSuites . "\n    </testsuites>", $content);
    }

    if (!str_contains($content, 'PHPUnitExtension')) {
        $content = str_replace(
            '<extensions>',
            '<extensions>' . "\n        " . '<bootstrap class="DAMA\DoctrineTestBundle\PHPUnit\PHPUnitExtension"/>',
            $content
        );
    }

    file_put_contents($phpunitFile, $content);
}
