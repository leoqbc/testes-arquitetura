<?php

declare(strict_types=1);

namespace Tests\Architecture;

use PHPat\Selector\Selector;
use PHPat\Test\Builder\Rule;
use PHPat\Test\PHPat;

/**
 * Protege os limites entre as camadas e as convenções estruturais do projeto.
 * Cada método declara uma regra que o PHPAT verifica durante a análise estática.
 */
final class ArchitectureRules
{
    /**
     * Mantém o domínio isolado das camadas externas.
     * Regras de negócio não podem conhecer casos de uso nem detalhes de infraestrutura.
     */
    public function test_domain_does_not_depend_on_outer_layers(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('DifferDev\Domain'))
            ->shouldNot()
            ->dependOn()
            ->classes(
                Selector::inNamespace('DifferDev\Application'),
                Selector::inNamespace('DifferDev\Infrastructure'),
            )
            ->because('Domain must remain independent from outer layers');
    }

    /**
     * Impede que os casos de uso dependam diretamente de implementações técnicas.
     * A aplicação pode usar contratos do domínio, mas não classes da infraestrutura.
     */
    public function test_application_does_not_depend_on_infrastructure(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('DifferDev\Application'))
            ->shouldNot()
            ->dependOn()
            ->classes(Selector::inNamespace('DifferDev\Infrastructure'))
            ->because('Application must not depend on infrastructure details');
    }

    /**
     * Exige que todas as entidades de domínio sejam finais.
     * Isso evita que herança altere invariantes e comportamentos definidos pelo domínio.
     */
    public function test_domain_entities_are_final(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('DifferDev\Domain\Entity'))
            ->should()
            ->beFinal()
            ->because('Domain entities must not be extended');
    }

    /**
     * Exige que os serviços de aplicação sejam finais.
     * Casos de uso devem variar por composição de dependências, sem subclasses.
     */
    public function test_application_services_are_final(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('DifferDev\Application\Service'))
            ->should()
            ->beFinal()
            ->because('Application services must not be extended');
    }

    /**
     * Garante que os repositórios do domínio sejam apenas contratos.
     * As implementações concretas pertencem às camadas externas ao domínio.
     */
    public function test_domain_repositories_are_interfaces(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('DifferDev\Domain\Repository'))
            ->should()
            ->beInterface()
            ->because('Domain repositories must define contracts');
    }
}
