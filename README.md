# yivoff/jwt-refresh-bundle

[![Latest Stable Version](http://poser.pugx.org/yivoff/jwt-refresh-bundle/v)](https://packagist.org/packages/yivoff/jwt-refresh-bundle)
[![PHP Version Require](http://poser.pugx.org/yivoff/jwt-refresh-bundle/require/php)](https://packagist.org/packages/yivoff/jwt-refresh-bundle)
[![Total Downloads](http://poser.pugx.org/yivoff/jwt-refresh-bundle/downloads)](https://packagist.org/packages/yivoff/jwt-refresh-bundle)
[![Latest Unstable Version](http://poser.pugx.org/yivoff/jwt-refresh-bundle/v/unstable)](https://packagist.org/packages/yivoff/jwt-refresh-bundle)
[![License](http://poser.pugx.org/yivoff/jwt-refresh-bundle/license)](https://packagist.org/packages/yivoff/jwt-refresh-bundle)
![Tests](https://github.com/yivi/YivoffJwtRefreshBundle/actions/workflows/bundle_tests.yaml/badge.svg)
[![codecov](https://codecov.io/gh/yivi/YivoffJwtRefreshBundle/branch/master/graph/badge.svg?token=4JDTQ4IDN7)](https://app.codecov.io/gh/yivi/YivoffJwtRefreshBundle)

* [Description](#description)
* [Requirements](#requirements)
* [Installation and setup](#installation-and-setup)
  * [Installation](#installation)
  * [Token Provider Implementation](#token-provider-implementation)
    * [Purgable Provider](#purgable-provider)
  * [Security Integration](#security-integration) 
  * [Bundle Configuration](#bundle-configuration)
  * [Purge Command](#purge-command)
* [Usage](#usage)

## Description 

This package provides a way to generate "refresh tokens" that users can use to obtain a new authorization token (JWT)
when the previous one expires. This is a companion for [lexik/LexikJWTAuthenticationBundle], and it is not usable on its
own.

The package does not make any assumptions about the persistence layer for storing the refresh tokens. You can use any
backend or library (Mysql, Mongo, Redis, flat-file, etc) as long as there is a service that implements a basic interface
provided by the package: [`RefreshTokenProviderInterface`][1]

Tokens are stored with an identifier and a hashed verifier, instead of a plain-text verifier, for added security.

Each refresh-token can only be used once to get a new auth-token. When used, the old refresh-token is deleted, and a new
refresh-token is generated.

You should setup the time-to-live for the refresh-tokens to be significantly higher than the time to live of the
auth-tokens. 

## Requirements

Requires PHP 8+, Symfony 5.3+

## Installation and Setup

### Installation 
```bash
$ composer require yivoff/jwt-refresh-bundle
```
### Token Provider Implementation

This package makes no assumptions about the nature of your token provider. To be able to use it you'll need to implement
your own, either a regular Doctrime ORM repository or whatever better suits your project.

You'll need to have a service that implements `RefreshTokenProviderInterface`, and then on the bundle configuration, on
`yivoff_jwt_refresh.token_provider_service` you'll write down the service ID that the bundle will use for
getting/adding/removing tokens.

This service is responsible, directly or indirectly, of mediating with your persistance layer of choice, and  should
return/accept [`RefreshTokenInterface`][2] instances. Either your application token entity implements this interface
directly, or your token-provider adapts between your native entities, and the provided [`RefreshToken`][3] class.

#### Purgable Provider

Your token provider can additionally implement `PurgableRefreshTokenProviderInterface`, to have a convenience method to 
clear up all the stale tokens. This is necessary if you want to use the included [purge command](#purge-command)

### Security integration

On the same firewall where the JWT Authenticator provides with a login check, setup a new guard authenticator provided
by this bundle (`Yivoff\JwtTokenRefresh\Security\Authenticator`).

E.g, for a typical configuration:

```yaml
firewalls:
    login:
        pattern:  ^/login
        stateless: true
        anonymous: true
        provider: users_in_memory
        custom_authenticators:
          - Yivoff\JwtTokenRefresh\Security\Authenticator
        json_login:
            check_path:               /login_check
            success_handler:          lexik_jwt_authentication.handler.authentication_success
            failure_handler:          lexik_jwt_authentication.handler.authentication_failure 
```

Notice the content for `firewall.login.guard.authenticators`.

### Bundle Configuration:
**Yaml**
```yaml
yivoff_jwt_refresh:
    token_provider_service: 'App\Repository\AuthRefreshTokenRepository'
    token_ttl: 3600
    parameter_name: 'refresh_token'
```

**XML**
```xml
<yivoff_jwt_refresh xmlns="https://yivoff.com/schema/dic/jwt_refresh_bundle">
    <provider_service>App\Infrastructure\Redis\Repository\AuthRefreshTokenRepository</provider_service>
    <parameter_name>refresh_token</parameter_name>
    <token_ttl>3600</token_ttl>
</yivoff_jwt_refresh>
```

* `token_provider_service` 

   This is a **required** key. The string value must be the _id_ for a service that implements
`RefreshTokenProviderInterface`.

* `token_ttl`

   The bundle provides a default value of 3600. Change it if you want the token to be available for more or less time.

* `parameter_name`

    Name of the HTTP `POST` parameter that will hold the refresh token. `refresh_token` by default.

### Purge command
If `symfony/console` is installed on your project, and your Token Provider implements
`PurgableRefreshTokenProviderInterface`, you can use a command to delete all the existing tokens that have already
expired.

The command can simply be executed by running `bin/console yivoff:jwt_refresh:purge_expired_tokens`. On non-error
conditions, it produces no output.

### Usage

On any regular JSON authentication, the bundle will inject a refresh token on a field named as the `parameter_name`
defined on the configuration. A typical request/response would be:

**Request**
```http request
POST http://localhost:7099/login_check
Content-Type: application/json

{
  "username": "john_user",
  "password": "abcd"
}
```
**Response**
```http request
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
same login path with regular `POST` call with a HTTP parameter with the same name and value that we received previously:

```http request
POST http://localhost:7099/login_check
Content-Type: application/x-www-form-urlencoded

refresh_token=bd8b1a304dc39dda3d10a38788b2ebf7:f52ac998773d552a0c639c2f85ffa5f2e18df2f1a3f528c9ddc3fcd8c6ba2f31
```
### Events

If you want your application to react to successful or failed refresh attempts (logging, etc.), the library emits events
that you can listen to.

#### Failure

When the refresh attempt fails for whatever reason, the library emits a `Yivoff\JwtRefreshBundle\Event\JwtRefreshTokenFailed`
event. 

The event has three public properties:

* `?string tokenId`: The identifier for the refresh token. This will be null if the payload was invalid, and no
   identifier could be retrieved from the request.
* `?string userIdentifier`: The identifier for the user that ows the token. this will be null if the payload was
   invalid, or if we couldn't find a token for the request `tokenId`.
* `FailType $failType`: This is an enum that describes the failure type encountered:
  * `FailType::PAYLOAD`: Payload could not be parsed.
  * `FailType::NOT_FOUND`: Token by this id could not be found.
  * `FailType::INVALID`:  Token was found, but verifier was invalid.
  * `FailType::EXPIRED`: Token was found, but it was already expired.

#### Success

On success, a `Yivoff\JwtRefreshBundle\Event\JwtRefreshTokenSucceeded` event is emitted. This simply includes the
properties:

* `string tokenId`: the identifier for the refresh token
* `string userIdentifier`: the identifier for the user that owns the token

[1]: https://github.com/yivi/YivoffJwtRefreshBundle/blob/master/Contracts/RefreshTokenProviderInterface.php
[2]: https://github.com/yivi/YivoffJwtRefreshBundle/blob/master/Contracts/RefreshTokenInterface.php
[3]: https://github.com/yivi/YivoffJwtRefreshBundle/blob/master/Model/RefreshToken.php
[4]: https://github.com/lexik/LexikJWTAuthenticationBundle
