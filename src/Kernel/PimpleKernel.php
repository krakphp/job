<?php

namespace Krak\Job\Kernel;

use Krak\Job,
    Krak\Mw,
    Krak\AutoArgs,
    Pimple\Container;

class PimpleKernel extends AbstractKernelDecorator
{
    private $container;

    public function __construct(Job\Kernel $kernel, Container $container) {
        parent::__construct($kernel);
        $this->container = $container;

        $wrap = function($stack) {
            return $stack->withContext(new Mw\Context\PimpleContext($this->container));
        };

        $this->kernel->producer($wrap);
        $this->kernel->consumer($wrap);
        $this->kernel->scheduleLoop($wrap);
        $this->kernel->autoArgsContext(function($context) {
            $context['pimple'] = $this->container;
            return $context;
        });
    }
}
