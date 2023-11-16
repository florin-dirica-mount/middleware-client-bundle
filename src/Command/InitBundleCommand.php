<?php

namespace Horeca\MiddlewareClientBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InitBundleCommand extends Command
{
    protected static $defaultName = 'horeca:middleware-client:init';

    // TODO: generate admin & fix provider class generation

    private ?string $orderNotificationTransport;
    private ?string $providerApiClass;
    private string  $projectDir;
    private string $providerCredentialsClass;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct();

        $this->orderNotificationTransport = (string) $container->getParameter('horeca.order_notification_messenger_transport') ?: 'hmc_order_notification';
        $this->providerApiClass = (string) $container->getParameter('horeca.provider_api_class');
        $this->providerCredentialsClass = (string) $container->getParameter('horeca.provider_credentials_class');
        $this->projectDir = (string) $container->getParameter('kernel.project_dir');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Check if provider api class exists...');
        if (!class_exists($this->providerApiClass)) {
            $output->writeln('Generate ProviderApi class from template...');

            $this->generateProviderOrderClassFromTemplate('App\\VO\\ProviderOrder');
            $this->generateProviderApiClassFromTemplate($this->providerApiClass);
            $this->generateTenantCredentialsClassFromTemplate($this->providerCredentialsClass);
        }

        return 0;
    }

    private function generateProviderOrderClassFromTemplate(string $fqcn): void
    {
        $namespace = $this->getNamespaceFromFqcn($fqcn);
        $className = $this->getClassNameFromFqcn($fqcn);
        $filePath = $this->getFilePathFromFqcn($fqcn);
        $tpl =
            "<?php

namespace $namespace;

use Horeca\MiddlewareClientBundle\VO\Provider\ProviderOrderInterface;

class $className implements ProviderOrderInterface
{

}
        ";

        $this->writeFile($filePath, $tpl);
    }

    private function generateProviderCredentialsClassFromTemplate(string $fqcn): void
    {
        $namespace = $this->getNamespaceFromFqcn($fqcn);
        $className = $this->getClassNameFromFqcn($fqcn);
        $filePath = $this->getFilePathFromFqcn($fqcn);
        $tpl =
            "<?php

namespace $namespace;

use Horeca\MiddlewareClientBundle\VO\Provider\ProviderCredentialsInterface;

class $className implements ProviderCredentialsInterface
{

}
        ";

        $this->writeFile($filePath, $tpl);
    }

    private function generateProviderApiClassFromTemplate(string $providerApiClass): void
    {
        $namespace = $this->getNamespaceFromFqcn($providerApiClass);
        $className = $this->getClassNameFromFqcn($providerApiClass);

        $tpl =
            "<?php

namespace $namespace;

use Horeca\MiddlewareClientBundle\Service\ProviderApiInterface;
use Horeca\MiddlewareClientBundle\VO\Provider\BaseProviderOrderResponse;
use Horeca\MiddlewareClientBundle\VO\Provider\ProviderCredentialsInterface;
use Horeca\MiddlewareClientBundle\VO\Provider\ProviderOrderInterface;
use Horeca\MiddlewareCommonLib\Model\Cart\ShoppingCart;
use App\VO\ProviderOrder;
use App\VO\ProviderCredentials;

class $className implements ProviderApiInterface
{

    public function getProviderOrderClass(): string
    {
        return ProviderOrder::class;
    }

    public function getProviderCredentialsClass(): string
    {
        return ProviderCredentials::class;
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

        $filePath = $this->getFilePathFromFqcn($providerApiClass);

        $this->writeFile($filePath, $tpl);
    }

    private function generateTenantCredentialsClassFromTemplate(?string $tenantCredentialsClassName): void
    {
        $namespace = $this->getNamespaceFromFqcn($tenantCredentialsClassName);
        $className = $this->getClassNameFromFqcn($tenantCredentialsClassName);

        $tpl =
            "<?php

namespace $namespace;

use Doctrine\ORM\Mapping as ORM;
use Horeca\MiddlewareClientBundle\Entity\BaseProviderCredentials;

#[ORM\Entity(repositoryClass: \"App\Repository\TenantCredentialsRepository\")]
class $className extends BaseProviderCredentials
{
    // add your custom fields here
}
        ";

        $filePath = $this->getFilePathFromFqcn($tenantCredentialsClassName);

        $this->writeFile($filePath, $tpl);
    }

    private function getFilePathFromFqcn(string $fqcn): string
    {
        return dirname($fqcn);
    }

    private function getNamespaceFromFqcn(string $ns): string
    {
        $parts = explode('\\', $ns);

        return $parts[count($parts) - 1];
    }

    private function getClassNameFromFqcn(string $fqcn): string
    {
        $parts = explode('\\', $fqcn);

        return str_replace(['/', '\\' . $parts[0] . '\\'], ['\\', '\\'], "{$this->projectDir}/src/$fqcn.php");
    }

    private function writeFile(string $path, string $content)
    {
        if (!file_exists($path)) {
            if (!file_exists(dirname($path))) {
                // -rw-rw-rw-
                mkdir(dirname($path), 0666, true);
            }

            file_put_contents($path, $content);
        }
    }
}
