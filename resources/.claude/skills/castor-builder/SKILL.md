---
name: castor-builder
description: >
  Run commands inside the project's PHP container via Castor.
  Use this skill to know how to run Symfony/Doctrine/Composer commands
  in the project's Docker environment.
---

# Skill: Running commands in the PHP container

## Basic command

```bash
castor docker:builder -- <command>
```

All PHP/Symfony/Doctrine/Composer commands must go through `castor docker:builder --`.
The alias `castor builder` does NOT exist — always use `castor docker:builder --`.

## Common examples

```bash
# Doctrine migrations
castor docker:builder -- php bin/console doctrine:migrations:diff
castor docker:builder -- php bin/console doctrine:migrations:migrate --no-interaction

# Symfony cache
castor docker:builder -- php bin/console cache:clear

# Generate an Aropixel CRUD
castor docker:builder -- php bin/console aropixel:make:crud

# Composer
castor docker:builder -- composer require vendor/package

# PHPStan / CS Fixer (or use the dedicated castor aliases)
castor docker:builder -- vendor/bin/php-cs-fixer fix
castor docker:builder -- vendor/bin/phpstan analyse
```

## Notes

- Always use `castor docker:builder -- <command>`, with the `--` separator before the command.
- Do not use `castor ssh` (it does not exist).
- Castor handles TTY detection automatically (interactive vs non-interactive).
