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
