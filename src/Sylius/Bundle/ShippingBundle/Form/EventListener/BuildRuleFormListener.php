<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\ShippingBundle\Form\EventListener;

use Sylius\Component\Shipping\Checker\Registry\RuleCheckerRegistryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

/**
 * This listener adds configuration form to a rule,
 * if selected rule requires one.
 *
 * @author Saša Stamenković <umpirsky@gmail.com>
 */
class BuildRuleFormListener implements EventSubscriberInterface
{
    /**
     * @var RuleCheckerRegistryInterface
     */
    private $checkerRegistry;

    /**
     * @var FormFactoryInterface
     */
    private $factory;

    public function __construct(RuleCheckerRegistryInterface $checkerRegistry, FormFactoryInterface $factory)
    {
        $this->checkerRegistry = $checkerRegistry;
        $this->factory = $factory;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT   => 'preBind'
        );
    }

    public function preSetData(FormEvent $event)
    {
        $rule = $event->getData();
        $form = $event->getForm();

        if (null === $rule || null === $rule->getId()) {
            return;
        }

        $this->addConfigurationFields($form, $rule->getType(), $rule->getConfiguration());
    }

    public function preBind(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (empty($data) || !array_key_exists('type', $data)) {
            return;
        }

        $this->addConfigurationFields($form, $data['type']);
    }

    protected function addConfigurationFields(FormInterface $form, $ruleType, array $data = array())
    {
        $checker = $this->checkerRegistry->getChecker($ruleType);
        $configurationField = $this->factory->createNamed('configuration', $checker->getConfigurationFormType(), $data, array('auto_initialize' => false));

        $form->add($configurationField);
    }
}
