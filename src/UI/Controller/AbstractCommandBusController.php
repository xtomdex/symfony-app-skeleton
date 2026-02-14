<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Domain\Contract\CommandBusExceptionMapperInterface;
use App\Infrastructure\Form\FormErrorNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractCommandBusController extends AbstractController
{
    protected function exceptionMapper(): CommandBusExceptionMapperInterface
    {
        return $this->container->get(CommandBusExceptionMapperInterface::class);
    }

    protected function formErrorNormalizer(): FormErrorNormalizer
    {
        return $this->container->get(FormErrorNormalizer::class);
    }

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            CommandBusExceptionMapperInterface::class,
            FormErrorNormalizer::class,
        ]);
    }
}
