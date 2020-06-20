# Masa Git Server

### Dependencies

##### PHP Framework

Reference: http://slimframework.com/

##### Git interaction

Reference: https://github.com/lotharthesavior/git

##### OAuth2 library

Reference: https://oauth2.thephpleague.com/

##### Flysystem (filesystem library)

Reference: https://flysystem.thephpleague.com/

##### Clients Credential Workflow

Reference: https://tools.ietf.org/html/rfc6749#section-4.4

**Request**:

URL: http://git.dev/access_token

HTTP Verb: "POST"

Content-Type: application/x-www-form-urlencoded
```json
grant_type=client_credentials&client_id=1&client_secret=e776dbd85f227b0f6851d10eb76cdb04903b9632&scope=basic
```
The previous data is this:
```json
{
	"grant_type": "client_credentials",
	"client_id": "1",
	"client_secret": "secret",
	"scope": "basic"
}
```

**Response**
```json
{
  "token_type": "...",
  "expires_in": 3600,
  "access_token": "..."
}
```
**Persistent Data**:
1. **\oauth\Access Tokens**: 
2. **\oauth\Auth Codes**
**Description**: Instead of requesting authorization directly from the resource owner, the client directs the resource owner to an authorization server (via its user-agent as defined in [RFC2616]), which in turn directs the resource owner back to the client with the authorization code.
**Reference**: https://tools.ietf.org/html/rfc6749#section-1.3.1 
3. **\oauth\Clients**: client created for authorization in regard of other users
 
**Step-by-Step**

1. Generate Certificate with:

```ssh
openssl req -new -newkey rsa:2048 -nodes -keyout server_name.key -out server_name.csr
```

Obs.: to generaste a pub from the private, do as follow:

```sh
openssl rsa -in mykey.pem -pubout > mykey.pub
```
Reference: https://stackoverflow.com/questions/5244129/use-rsa-private-key-to-generate-public-key#5246045

Alternative - Self Signed Certificate:
```sh
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout server_name.key -out server_name.crt
```
Reference: https://www.digitalocean.com/community/tutorials/how-to-create-a-self-signed-ssl-certificate-for-apache-in-ubuntu-16-04

2. Create the "data" directory using the server user, so you avoid problem with permissions:

Is using swoole:

```sh
cp data.sample data
```

If using webservers like nginx or apache:

```sh
cp data.sample data && sudo chown -R www-data data
```

3. Configure Apache server

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

4. Give permission to www-data to the folder of the project:

```sh
sudo chown -R www-data {directory of the project}
```

5. Copy `config.json.sample` file to `config.json` and customize the file.

```sh
cp config.json.sample config.json
```

Notice that:

- the `database-address` key must be a a full path to the data directory.
- the `private_key` and `public_key` keys must be full paths to the actual keys generated in the Step 1.
- possible values for `env` are (can be found in the file `src/constants.php`):
  - develop
  - staging
  - production

6. Start your Swoole Server

Make sure that the key `swoole` at your `config.json` is set to `true` to be able to run the Swoole Server.

```sh
php index.php
```

7. Copy the `config.json.tests.sample` to `config.json.tests` for the fields that you want to customize in order to run properly the tests in your machine.

##### Cofiguration Customization

Is possible to use different configuration items accoridng to environment. for that, set the `env` of the config file to develop, as an example, and create a file named `config.json-develop` with the same items of the `config.json`, but with the settings for that environment. The ones present there will overwrite the original ones at the `config.json` file when that file points to that env.