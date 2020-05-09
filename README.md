# YivoffJwtRefreshBundle

This package provides a way to generate "refresh tokens" that users can use to obtain a new authorization token (JWT)
when the previous one expires. This is a companion for [lexik/LexikJWTAuthenticationBundle], and it is not usable on its
own.

Requires PHP 7.4+.

## Description 

This package does not make any assumptions about the persistence layer for storing the refresh tokens. You can use any
backend or library (Mysql, Mongo, Redis, flat-file, etc) as long as there is a service that implements a basic interface
provided by the package: [`RefreshTokenProviderInterface`][1]

Tokens are stored with an identifier and a hashed verifier, instead of a plain-text verifier, for added security.

Each refresh-token can only be used once to get a new auth-token. When used, the old refresh-token is deleted, and a new
refresh-token is generated.

You should setup the time-to-live for the refresh-tokens to be significantly higher than the time to live of the
auth-tokens. 

### Installation

```bash
$ composer require yivoff/jwt-refresh
```

### Basic Requirements
Write an implementation for `RefreshTokenProviderInterface`. Use `yivoff_jwt_refresh.token_provider_service` to tell the
bundle to use it to get/add/remove tokens.

This service is responsible, directly or indirectly, of mediating with your persistance layer of choice, and  should
return/accept [`RefreshTokenInterface`][2] instances. Either your application token entity implements this interface
directly, or your token-provider adapts between your native entities, and the provided [`RefreshToken`][3] class.

### Security integration

On the same firewall where the JWT Authenticator provides with a login check, setup a new guard authenticator provided
by this bundle (`Yivoff\JwtTokenRefresh\Security\Authenticator`).

E.g, for a typical configuration:

```
firewalls:
    login:
        pattern:  ^/login
        stateless: true
        anonymous: true
        provider: users_in_memory
        guard:
            authenticators:
                - Yivoff\JwtTokenRefresh\Security\Authenticator
        json_login:
            check_path:               /login_check
            success_handler:          lexik_jwt_authentication.handler.authentication_success
            failure_handler:          lexik_jwt_authentication.handler.authentication_failure 
```

Notice the content for `firewall.login.guard.authenticators`.

### Bundle Configuration:
```yaml
yivoff_jwt_refresh:
    token_provider_service: 'App\Repository\AuthRefreshTokenRepository'
    token_ttl: 3600
    parameter_name: 'refresh_token'
```

* `token_provider_service` 

   This is a **required** key. The string value must be the _id_ for a service that implements
`RefreshTokenProviderInterface`.

* `token_ttl`

   The bundle provides a default value of 3600. Change it if you want the token to be available for more or less time.

* `parameter_name`

    Name of the HTTP `POST` parameter that will hold the refresh token. `refresh_token` by default.

### Usage

On any regular JSON authentication, the bundle will inject a refresh token on a field named as the `parameter_name`
defined on the configuration. A typical request/response would be:

**Request**
```
POST http://localhost:7099/login_check
Content-Type: application/json

{
  "username": "john_user",
  "password": "abcd"
}
```
**Response**
```
HTTP/1.1 200 OK
Date: Sat, 09 May 2020 16:01:37 GMT
Connection: close
Content-Type: application/json

{
  "token": "ey...token...131",
  "refresh_token": "bd8b1a304dc39dda3d10a38788b2ebf7:f52ac998773d552a0c639c2f85ffa5f2e18df2f1a3f528c9ddc3fcd8c6ba2f31"
}
```

It is not necessary to register a new route for the "refresh" path. To get a new authentication JWT, you simply call the
same login path with `POST` HTTP parameter with the same name and value that we received previously:

```
POST http://localhost:7099/login_check
Content-Type: application/x-www-form-urlencoded

refresh_token=bd8b1a304dc39dda3d10a38788b2ebf7:f52ac998773d552a0c639c2f85ffa5f2e18df2f1a3f528c9ddc3fcd8c6ba2f31
```



[1]: https://github.com/yivi/YivoffJwtRefreshBundle/blob/master/Contracts/RefreshTokenProviderInterface.php
[2]: https://github.com/yivi/YivoffJwtRefreshBundle/blob/master/Contracts/RefreshTokenInterface.php
[3]: https://github.com/yivi/YivoffJwtRefreshBundle/blob/master/Model/RefreshToken.php
[4]: https://github.com/lexik/LexikJWTAuthenticationBundle
