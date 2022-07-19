Configuration
=====

#### Environment variables

- define following environment variables

```dotenv
HORECA_BASE_URL="The horeca backend URL"
HORECA_API_KEY="The API key used to send requests to horeca backend"
HORECA_SHARED_KEY="The API key horeca backend uses to send requests to middleware api"
HORECA_MIDDLEWARE_CLIENT_ID="The middleware client id configured in horeca backend. Used to send requests to horeca backend"
HORECA_ENABLE_REQUEST_EXCEPTION_LOGGING="Any request exception will be logged to request_logs table"
```

- add bundle configuration in `config/packages/horeca_middleware_client.yaml`

```yaml
horeca_middleware_client:
    base_url: '%env(resolve:HORECA_BASE_URL)%'
    api_key: '%env(resolve:HORECA_API_KEY)%'
    shared_key: '%env(resolve:HORECA_SHARED_KEY)%'
    middleware_client_id: '%env(resolve:HORECA_MIDDLEWARE_CLIENT_ID)%'
    enable_request_exception_logging: '%env(resolve:HORECA_ENABLE_REQUEST_EXCEPTION_LOGGING)%'

    provider_api_class: App\Service\ProviderApi
    order_notification_messenger_transport: hmc_order_notification
```

#### Post-install

- run command `php bin/console horeca:middleware-client:init` after package initial installation
