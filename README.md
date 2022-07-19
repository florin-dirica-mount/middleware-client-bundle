Configuration
=====

### Define environment variables

```dotenv
HORECA_BASE_URL="The horeca backend URL"
HORECA_API_KEY="The API key used to send requests to horeca backend"
HORECA_SHARED_KEY="The API key horeca backend uses to send requests to middleware api"
HORECA_MIDDLEWARE_CLIENT_ID="The middleware client id configured in horeca backend. Used to send requests to horeca backend"
HORECA_ENABLE_REQUEST_EXCEPTION_LOGGING="Any request exception will be logged to request_logs table"
```

### Add bundle configuration in `config/packages/horeca_middleware_client.yaml`

```yaml
horeca_middleware_client:
    provider_api_class: App\Service\ProviderApi
    order_notification_messenger_transport: hmc_order_notification
```

### Register bundle routes in `config/routes.yaml`

```yaml
horeca:
    resource: '@HorecaMiddlewareClientBundle/Resources/config/horeca_routes.yaml'
    prefix: /
```

### Update symfony/messenger with transport used by horeca bundle `config/packages/messenger.yaml`

```yaml
framework:
    messenger:
        transports:
            # (...)
            hmc_order_notification: '%env(MESSENGER_TRANSPORT_DSN)%?queue_name=hmc_order_notification'
            # (...)
        routing:
            'Horeca\MiddlewareClientBundle\Message\OrderNotificationMessage': hmc_order_notification
            # (...)
```

#### Post-install

- run command `php bin/console horeca:middleware-client:init` after package initial installation
