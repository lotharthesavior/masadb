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
ssh-keygen -t rsa -b 4096
```

```ssh
openssl req -new -newkey rsa:2048 -nodes -keyout server_name.key -out server_name.csr
```

Obs.: to generaste a pub from the private, do as follow:

```sh
openssl rsa -in mykey.pem -pubout > mykey.pub
```
Reference: https://stackoverflow.com/questions/5244129/use-rsa-private-key-to-generate-public-key#5246045

Self Signed Certificate:
```sh
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout server_name.key -out server_name.crt
```
Reference: https://www.digitalocean.com/community/tutorials/how-to-create-a-self-signed-ssl-certificate-for-apache-in-ubuntu-16-04

2. Create the "data" directory using the server user, so you avoid problem with permissions:

```sh
sudo -u www-data mkdir data
```

```sh
cd data
```

```sh
sudo -u www-data git init
```

3. Configure Apache server

The www-data must have enough permission to access the data through git.
To do that, you can edit the envvars of apache with this:
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

###### OR Run https://{domain}/install.php and the steps 1, 3 and 4.

4. Configure Nginx server (laravel homestead vm)

In this environment, there is the necessity to configure the permission of the synced folder. To do this you can insert this in the Vagrantfile:

```sh
config.vm.synced_folder "./shared", "/home/vagrant/Code", :owner => "www-data", :group => "www-data"
```

This will give permission to www-data to change the files inside the necessary folder.

It might be required to give the permission manually too:

```sh
sudo chown -R www-data /home/vagrant/Code/masadb
```

##### Cofiguration Customization

Is possible to use different configuration items accoridng to environment. for that, set the `env` of the config file to develop, as an example, and create a file named `config.json-develop` with the same items of the `config.json`, but with the settings for that environment. The ones present there will overwrite the original ones at the `config.json` file when that file points to that env.