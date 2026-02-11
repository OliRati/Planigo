### How to run this Symfony Web Application
#### Download the project
- Clone this repository
- go to repository directory
- To restore all project dependencies use the following command
```bash
composer update
```
#### Setup your local configuration in .env.local
Create .env.local with the database credential for your system

##### For Windows with Wamp it looks like

```php
###> Local database secret
DATABASE_URL="mysql://root:@127.0.0.1:3306/planigo?serverVersion=8.0.32&charset=utf8mb4"
```
Then create the database with the following command
```bash
php bin/console doctrine:database:create
```

#### For linux

##### Create new database if doesn't exists

In order to be more secure, don't share the whole system database, create a user and the root database inside the mysql inteface as a root user

```sql
-- Create the new Database
CREATE DATABASE planigo
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- Create a user identifies by is name / password
CREATE USER 'planigo_user'@'localhost'
  IDENTIFIED BY 'mot_de_passe_solide';

-- Grant necessaries privileges
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP
ON planigo.*
TO 'planigo_user'@'localhost';

-- Flush changes
FLUSH PRIVILEGES;
```

No need anymore to be a root user

Create .env.local with the database credential for your system

```php
###> Local database secret
DATABASE_URL="mysql://planigo_user:mot_de_passe_solide@localhost:3306/planigo?serverVersion=8.0.32&charset=utf8mb4"
```

Replace ```planigo_user:mot_de_passe_solide``` with your credentials.

Run this to create symfony internal tables

```bash
php bin/console doctrine:migrations:sync-metadata-storage
```

#### For all users

When database creation is done, set it up with

```bash
php bin/console doctrine:migrations:migrate
```

#### Prepare for running...
##### For Linux
As a user you need to define a directory accessible to you for storing php sessions.

For example create ~/php_sessions directory using command :

```bash
mkdir ~/php_sessions
```

Then run the Symfony project with following command :

```bash
php -d session.save_path=${HOME}/php_sessions -S 127.0.0.1:8000 -t public public/index.php
```

##### For Windows

You can use Symfony Cli as follow.
( It might also works on Linux it depends of your distribution security policies )

```bash
symfony server:start
```
