# Masa Git Server

### Dependencies

##### PHP Framework

Reference: http://slimframework.com/

##### Git interaction

Reference: https://github.com/coyl/git

##### OAuth2 library

Reference: https://oauth2.thephpleague.com/

##### Clients Credential Workflow

**Reference**: https://tools.ietf.org/html/rfc6749#section-4.4

**Request**:

URL: http://git.dev/access_token

HTTP Verb: "POST"

Content-Type: application/x-www-form-urlencoded
```json
{
	"grant_type": "client_credentials",
	"client_id": "test",
	"client_secret": "secret",
	"scope": "basic update"
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
1. **\Users**: common database for authorization
2. **\oauth\Access Tokens**: 
3. **\oauth\Auth Codes**
**Description**: Instead of requesting authorization directly from the resource owner, the client directs the resource owner to an authorization server (via its user-agent as defined in [RFC2616]), which in turn directs the resource owner back to the client with the authorization code.
**Reference**: https://tools.ietf.org/html/rfc6749#section-1.3.1 
4. **\oauth\Clients**: client created for authorization in regard of other users
 
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
2. create the "data" directory using the server user, so you avoid problem with permissions:
```sh
sudo -u www-data mkdir data
```
```sh
cd data
```
```sh
sudo -u www-data git init
```

