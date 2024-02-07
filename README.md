# Disclaimer

- **project is not intended for public use**

# Installation

- update `composer.json` with the bundle recipe

```json
{
    "require": {
        "php": ">=8.0",
        "horeca/middleware-common-lib": "^1.0.2",
        "horeca/middleware-client-bundle": "^0.4"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "https://gitlab.com/horeca-universal/libs/middleware-common-lib.git"
        },
        {
            "type": "vcs",
            "url": "https://github.com/florin-dirica-mount/middleware-client-bundle.git"
        }
    ],
    "extra": {
        "symfony": {
            "allow-contrib": true,
            "require": "^5.0",
            "endpoint": [
                "https://api.github.com/repos/florin-dirica-mount/symfony-recipes/contents/index.json",
                "flex://defaults"
            ]
        }
    }
}
```

Configuration
=====

### Define environment variables

```dotenv
HORECA_BASE_URL="The horeca backend URL"
HORECA_API_KEY="The API key used to send requests to horeca backend"
HORECA_SHARED_KEY="The API key horeca backend uses to send requests to middleware api"
HORECA_MIDDLEWARE_CLIENT_ID="The middleware client id configured in horeca backend. Used to send requests to horeca backend"
```

### Add bundle configuration

- `config/packages/horeca_middleware_client.yaml`

```yaml
horeca_middleware_client:
    provider_api_class:                     App\Service\ProviderApi
    provider_credentials_class:             App\Entity\ProviderCredentials # this entity must extend Horeca\MiddlewareClientBundle\Entity\BaseProviderCredentials
    order_notification_messenger_transport: hmc_order_notification
```

- `config/routes/horeca_middleware_client.yaml`

```yaml
horeca_middleware_client:
    resource: "@HorecaMiddlewareClientBundle/Resources/config/routes.yaml"
```

- Create entity for tenant provider credentials at `App\Entity\ProviderCredentials`

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Horeca\MiddlewareClientBundle\Entity\BaseProviderCredentials;

#[ORM\Entity(repositoryClass: "App\Repository\TenantCredentialsRepository")]
class TenantCredentials extends BaseProviderCredentials
{
    // add your custom fields here
}
```

### Register bundle routes in `config/routes.yaml`

```yaml
horeca:
    resource: '../vendor/horeca/middleware-client-bundle/src/Controller/'
    prefix:   /
    type:     annotation
```

### Update symfony/messenger with transport used by horeca bundle `config/packages/messenger.yaml`

```yaml
framework:
    messenger:
        transports:
            hmc_map_tenant_order_to_provider:        '%env(resolve:MESSENGER_TRANSPORT_DSN)%?queue_name=hmc_map_tenant_order_to_provider'
            hmc_tenant_order_send_to_provider:       '%env(resolve:MESSENGER_TRANSPORT_DSN)%?queue_name=hmc_tenant_order_send_to_provider'
            hmc_order_notification_event:            '%env(resolve:MESSENGER_TRANSPORT_DSN)%?queue_name=hmc_order_notification_event'
            hmc_external_service_order_notification: '%env(resolve:MESSENGER_TRANSPORT_DSN)%?queue_name=hmc_external_service_order_notification'

        routing:
            'Horeca\MiddlewareClientBundle\Message\MapTenantOrderToProviderMessage':  hmc_map_tenant_order_to_provider
            'Horeca\MiddlewareClientBundle\Message\SendTenantOrderToProviderMessage': hmc_tenant_order_send_to_provider
            'Horeca\MiddlewareClientBundle\Message\OrderNotificationEventMessage':    hmc_tenant_order_notification_event
            'Horeca\MiddlewareClientBundle\Message\SendProviderOrderToTenantMessage': hmc_external_service_order_notification
```

#### Post-install

- run command `php bin/console horeca:middleware-client:init` after package initial installation

# TODO

- example recipe structure

```text
symfony/
    console/
        3.3/
            bin/
            manifest.json
    framework-bundle/
        3.3/
            config/
            public/
            src/
            manifest.json
    requirements-checker/
        1.0/
            manifest.json
```

- update symfony recipe, full config options:

```json
{
    "bundles": {
        "Symfony\\Bundle\\FrameworkBundle\\FrameworkBundle": [
            "all"
        ]
    },
    "copy-from-recipe": {
        "config/": "%CONFIG_DIR%/",
        "public/": "%PUBLIC_DIR%/",
        "src/": "%SRC_DIR%/"
    },
    "composer-scripts": {
        "cache:clear": "symfony-cmd",
        "assets:install --symlink --relative %PUBLIC_DIR%": "symfony-cmd"
    },
    "env": {
        "APP_ENV": "dev",
        "APP_SECRET": "%generate(secret)%"
    },
    "gitignore": [
        ".env",
        "/public/bundles/",
        "/var/",
        "/vendor/"
    ]
}
```

# Tenant Webhooks

- The order was sent to the provider system (deprecated)

```
POST /middleware/cart/{id}/confirm-provider-notified

Response: status 200
```

- Send an order received from the provider system

```
POST /middleware/order/{restaurant_id}
Body: ShoppingCart

Reponse: status 200
    horeca_order_id: string
```

- Receive order notification events

```
POST /middleware/notification/event
Body:
    event: hmc.order_notification.mapping_failed | hmc.order_notification.mapping_completed | hmc.order_notification.provider_notified | hmc.order_notification.provider_notification_failed
    notification:
        id: string 
        tenantObjectId: string
        providerObjectId: ?string
        status: mapped | notified | failed

Response: status 200
```