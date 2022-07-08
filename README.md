Configuration
=====

#### Environment variables

- defined following environment variables

```dotenv
HORECA_BASE_URL="The horeca backend URL"
HORECA_API_KEY="The API key used to send requests to horeca backend"
HORECA_SHARED_KEY="The API key horeca backend uses to send requests to middleware api"
HORECA_MIDDLEWARE_CLIENT_ID="The middleware client id configured in horeca backend. Used to send requests to horeca backend"
HORECA_ENABLE_REQUEST_EXCEPTION_LOGGING="Any request exception will be logged to request_logs table"
```

#### Symfony messenger

- add the following transport to `messenger.yaml`

```yaml
framework:
    messenger:
        transports:
            # https://symfony.com/doc/current/messenger.html#transport-configuration
            # async: '%env(MESSENGER_TRANSPORT_DSN)%'
            # failed: 'doctrine://default?queue_name=failed'
            # sync: 'sync://'
            hmc_order_notification: "%env(MESSENGER_TRANSPORT_DSN)%?queue_name=hmc_order_notification"

            failed: 'doctrine://default?queue_name=failed'
            sync: 'sync://'

        routing:
            # Route your messages to the transports
            # 'App\Message\YourMessage': async
            'Horeca\MiddlewareClientBundle\Message\OrderNotificationMessage': hmc_order_notification
```

#### Application services
