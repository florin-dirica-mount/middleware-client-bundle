<?php

namespace Horeca\MiddlewareClientBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

class InitBundleCommand extends Command
{
    protected static $defaultName = 'horeca:middleware-client:init';

    private ?string $orderNotificationTransport;
    private ?string $providerApiClass;
    private string  $projectDir;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct();

        $this->orderNotificationTransport = (string) $container->getParameter('horeca.order_notification_messenger_transport') ?: 'hmc_order_notification';
        $this->providerApiClass = (string) $container->getParameter('horeca.provider_api_class');
        $this->projectDir = (string) $container->getParameter('kernel.project_dir');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Check if provider api class exists...');
        if (!class_exists($this->providerApiClass)) {
            $output->writeln('Generate ProviderApi class from template...');

            $this->generateProviderApiClassFromTemplate($this->providerApiClass);
        }

        // configure transport if missing
        $messengerConfigFile = $this->projectDir . '/config/packages/messenger.yaml';
        $messengerConfig = Yaml::parseFile($messengerConfigFile);
        if (!in_array($this->orderNotificationTransport, (array) $messengerConfig['framework']['messenger']['transports'])) {
            $output->writeln('Add new messenger transport to `config/packages/messenger.yaml`...');

            $messengerConfig['framework']['messenger']['transports'][$this->orderNotificationTransport] = "%env(MESSENGER_TRANSPORT_DSN)%?queue_name={$this->orderNotificationTransport}";
            file_put_contents($messengerConfigFile, Yaml::dump($messengerConfig, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK));
        }

        return 0;
    }

    private function generateProviderApiClassFromTemplate(string $providerApiClass)
    {
        $parts = explode('\\', $providerApiClass);
        $namespace = dirname($providerApiClass);
        $className = $parts[count($parts) - 1];

        $tpl =
            "<?php

namespace $namespace;

use Horeca\MiddlewareClientBundle\Service\ProviderApiInterface;
use Horeca\MiddlewareClientBundle\VO\Provider\BaseProviderOrderResponse;
use Horeca\MiddlewareClientBundle\VO\Provider\ProviderCredentialsInterface;
use Horeca\MiddlewareClientBundle\VO\Provider\ProviderOrderInterface;
use Horeca\MiddlewareCommonLib\Model\Cart\ShoppingCart;

class $className implements ProviderApiInterface
{

    public function getProviderOrderClass(): string
    {
        // TODO: Implement getProviderOrderClass() method.
    }

    public function getProviderCredentialsClass(): string
    {
        // TODO: Implement getProviderCredentialsClass() method.
    }

    public function saveOrder(ProviderOrderInterface \$order, ProviderCredentialsInterface \$credentials): BaseProviderOrderResponse
    {
        // TODO: Implement saveOrder() method.
    }

    public function mapShoppingCartToProviderOrder(ShoppingCart \$cart): ProviderOrderInterface
    {
        // TODO: Implement mapShoppingCartToProviderOrder() method.
    }

}
        ";

        $filePath = str_replace(['/', '\\' . $parts[0] . '\\'], ['\\', '\\'], "{$this->projectDir}/src/$namespace/$className.php");

        if (!file_exists($filePath)) {

            if (!file_exists(dirname($filePath))) {
                mkdir(dirname($filePath), 07777, true);
            }

            file_put_contents($filePath, $tpl);
        }
    }
}
