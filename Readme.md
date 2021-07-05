![MasaDB Logo](./public/img/masadb.png)

# MasaDB

MasaDB is a small app with the intent to manage files with native Git version control via a Restful API. It comes with an OAuth2 Server structure for Authorization. It allows the system to, not just protect the data, but also to allow Authorization to other apps with MasaDB's existent user.

The data here is kept in plain-text format within files. Tables in MasaDB are Git Repositories, and can be easily shared like such.



### TOC

- [Dependencies](#dependencies)
- [Oauth2 Clients Credential Workflow](#clients-credential-workflow)
- [Docker](#docker)
- [Parameters to Customize](#parameters-to-customize)
- [Data Structure](#data-structure)
- [Psyshell](#psyshell)



### Dependencies

##### Swoole Extension

Reference: https://www.swoole.co.uk/

##### PHP Framework

Reference: http://slimframework.com/

##### Git interaction

Reference: https://github.com/lotharthesavior/branch

##### OAuth2 library

Reference: https://oauth2.thephpleague.com/

##### Flysystem (filesystem library)

Reference: https://flysystem.thephpleague.com/

### Installation / Setup

1. Start by creating a `/certs` directory
2. Make sure you have mkcert installed ( if you have homebrew, it's easy, just do `brew install mkcert` && `mkcert -install`)
3. browse to `certs` directory, and run `mkcert local.masadb.com`
4. Add a record for this in your hosts file eg `127.0.0.1 local.masadb.com` (you can change this URL to what ever you'd like)
5. While still in the `certs` folder, run this command `openssl rsa -in local.masadb.com-key.pem -pubout > masadb.pub` 
6. Make sure the .pem files and the .pub files are all stored in 600 format `chmod 600 certs/*`
6. copy the `.env.sample` to `.env` - change the domain if you didn't use `local.masadb.com`
7. copy the `config.json.sample` to `config.json` - `cp config.json.sample config.json`
8. Create a `data` directory in the root folder `mkdir data`
9. copy the contents of data.sample into the data directory `cp -r data.sample/* data/`
10. We have a static IP setup for this service.  The prefix is stored in the .env file (10.2 by default), and the suffix is stored in the docker-compose file (0.7 by default) - making the full local IP address (10.2.0.7) - change these up if you would like
11. run `docker-compose up -d`


### Clients Credential Workflow

Reference: https://tools.ietf.org/html/rfc6749#section-4.4

**Request**:

```shell
curl --location -g --request POST '{{masadburl}}/access_token' \
--header 'Content-Type: application/json' \
--data-raw '{
    "grant_type": "client_credentials",
    "client_id": "{{clientid}}",
    "client_secret": "{{clientsecret}}",
    "scope": "basic"
}'
```

**Response**

```json
{
  "token_type": "Bearer",
  "expires_in": 3600,
  "access_token": "..."
}
```
With this Access Token in hand, the user will be able to proceed with persistent data within the system.

Such authorization is also saved in MasaDB like the rest of the data. Here is where to find it:

1. `\oauth\Access Tokens `
2. `\oauth\Auth Codes`
**Description**: Instead of requesting authorization directly from the resource owner, the client directs the resource owner to an authorization server (via its user-agent as defined in [RFC2616]), which in turn directs the resource owner back to the client with the authorization code.
**Reference**: https://tools.ietf.org/html/rfc6749#section-1.3.1 
3. `\oauth\Clients`: client created for authorization in regard of other users.

**Step-by-Step**

If using Swoole, follow only 1, 2, 5, 6 and 7 steps. If using another webserver such as Nginx or Apache, follow the steps 1, 2, 3, 4, 5 and 7.

1. Generate proper certificates to avoid problems with Authorization. For that, you can use this package: https://github.com/FiloSottile/mkcert . To use it, navigate to the `/certs` directory and generate the proper certificate:

```shell
mkcert {{domain}}
```

(for production certificates you can use letsencrypt)

for public key:

```shell
openssl rsa -in {private-key} -pubout > pubkey.pem
```

2. Create the "data" directory using the server user, so you avoid problem with permissions:

​    - If using swoole:

```sh
cp data.sample data
```

​    - If using webservers like nginx or apache:

```sh
cp data.sample data && sudo chown -R www-data data
```

3. Configure Apache server (jump this step if using Swoole)

The www-data must have enough permission to access the data through git.
To do that, you can edit the envvars of apache with this (I didn't test 
using nginx yet, let me know if you try it out):

```sh
sudo vim /etc/apache2/envvars
```

Add the following line to it:
```sh
export HOME=/var/www
```

Restart apache
```sh
sudo services apache2 restart
```

4. Give permission to www-data to the folder of the project: (jump this step if using Swoole)

```sh
sudo chown -R www-data {directory of the project}
```

5. Copy `config.json.sample` file to `config.json` and customize the file.

```sh
cp config.json.sample config.json
```

**Important**:

- the `database-address` key must be a a full path to the data directory.
- the `private_key` and `public_key` keys must be full paths to the actual keys generated in the Step 1.
- possible values for `env` are (can be found in the file `src/constants.php`):
  - `develop`
  - `staging`
  - `production`

6. Start your Swoole Server (jump this step if not using Swoole)

Make sure that the key `swoole` at your `config.json` is set to `true` to be able to run the Swoole Server.

```sh
php index.php
```

7. Copy the `config.json.tests.sample` to `config.json.tests` for the fields that you want to customize in order to run properly the tests in your machine.
8. Install Composer dependencies:

```shell
composer install
```



##### Cofiguration Customization

Is possible to use different configuration items accoridng to environment. for that, set the `env` of the config file to develop, as an example, and create a file named `config.json-develop` with the same items of the `config.json`, but with the settings for that environment. The ones present there will overwrite the original ones at the `config.json` file when that file points to that env.



### Docker

An image can be prepared with this command:

```shell
docker build --build-arg ENVIRONMENT_NAME='production' --build-arg RAW_FILES='true' .
```

To run after that, run this:

```shell
docker run -p 443:443 {image-id}
```



### Parameters to customize

ENVIRONMENT_NAME

Defines the environment, and can be either 'develop' or 'production'.

RAW_FILES

Defines the usage of raw files, and can be either true or false.

Raw files instead of [Bagit](http://www.digitalpreservation.gov/multimedia/videos/bagit0609.html) files. the raw files are useful for "normal" repos. the other side of it are records kept with the Bagit format intending to long term preservation.



### Data Structure

The Authorization is based on OAuth2 server. That said, every connection has a Client Id. Every single record is based on an User. The data structure is basically this:

```
data/
| - client_{id}
    | - {database}
        | - {record-identifier}
        | - {record-identifier}
| - client_{id}
    | - {database}
        | - {record-identifier}
...
```



### PsyShell

You can access the context of your MasaDB via shell by running the following command at the server where it is running:

```shell
php index.php --shell
```



#### Useful commands

- Model Usage

To access data stored, first you need to prepare the Generic model object:

```php
// Step 1: This will instantiate the Generic Model, with it, you'll have access to any record.
$generic = $container->get('Generic');

// Step 2: Set Client
$generic->setClientId(1);

// Step 3: Set Database
$generic->setDatabase('testdb');
```

Other Models might have data such as the `$database` property already set.



With the `Generic` model prepared, you can proceed with the basic data procedures:

**Search/Find**

```php
// Find
$generic->find('test.md'); // raw data type
$generic->find('1'); // json data type, it can also be "1.json"

// Search: raw data type
$generic->search('content', 'searched value'); // "content" is the only field available for raw data, json data can have any field in schema.

// Search: json data type
$generic->setJsonStructure(true); // this makes it possible to search by the fields in the schema
$generic->search('title', 'my title'); // searching by schema fields
```

**Create/Update**

```php
$generic->save([
    "id" => 1, // or "1.json" (raw data type has to be with the file extension)
    "content" => "data", // JSON format for json data type, any for raw data
]);
```

**Delete**

```php
$generic->delete('test.md'); // Raw data type
$generic->delete('1'); // Json data type, it can also be 1.json

// Find and delete
$generic->find(1)->delete();
$generic->search('content', 'data')->first()->delete();
```

