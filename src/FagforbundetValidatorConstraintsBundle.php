<?php

namespace Fagforbundet\ValidatorConstraintsBundle;

use Fagforbundet\ValidatorConstraintsBundle\Validator\Constraint\NotificationApiEmailAddressValidator;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpClient\ScopingHttpClient;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class FagforbundetValidatorConstraintsBundle extends AbstractBundle {

  public function configure(DefinitionConfigurator $definition): void {
    $definition->rootNode()
      ->addDefaultsIfNotSet()
      ->children()
        ->arrayNode('notification_api')
          ->addDefaultsIfNotSet()
          ->children()
            ->scalarNode('base_uri')->isRequired()->defaultValue('https://api.meldinger.fagforbundet.no')->end()
          ->end()
        ->end()
      ->end()
    ;
  }

  public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void {
    $alias = $this->getContainerExtension()->getAlias();
    $httpClientServiceId = $alias . 'http_client.notification_api';

    $container->services()
      ->set($httpClientServiceId, HttpClientInterface::class)
        ->factory([ScopingHttpClient::class, 'forBaseUri'])
        ->args([service('http_client.transport'), $config['notification_api']['base_uri']])
        ->tag('http_client.client')

      ->set($alias . '.validator.notification_api_email_address', NotificationApiEmailAddressValidator::class)
        ->args([service($httpClientServiceId)])
        ->tag('validator.constraint_validator')
    ;
  }

}
