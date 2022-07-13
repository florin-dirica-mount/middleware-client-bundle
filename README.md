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

#### Post-install

- run command `php bin/console horeca:middleware-client:init` after package initial installation
