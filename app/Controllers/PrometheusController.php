<?php

declare( strict_types = 1 );

namespace App\Controllers;

use Exception;
use App\Controllers\Controller;
use App\Controllers\MetricsController;
use Prometheus\Storage\InMemory;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use App\Helpers\ConfigHelper as Config;

class PrometheusController extends Controller
{
    /**
     * 
     * @var 
     */
    protected string $prefix;

    protected array $metrics;

    protected CollectorRegistry $registry;

    protected RenderTextFormat $renderer;

    /**
     * 
     * 
     */
    public function __construct() 
    {
        $this->prefix = strtolower(Config::env('APP_NAME', 'app'));

        $this->metrics = (new MetricsController)->getMetricsArray();

        $this->registry = new CollectorRegistry(new InMemory());

        $this->renderer = new RenderTextFormat();

        $this->registerMetrics ();
    }

    /**
     * 
     */
    protected function registerQueueLen() : void
    {
        $gauge = $this->registry->registerGauge(
            $this->prefix, 
            'queue_length', 
            'Current number of items stored on the main queue', 
            ['type']
        );
        $gauge->set(
            $this->metrics['queue_length'], 
            ['blue']
        );
        return;
    }

    /**
     * 
     */
    protected function registerActiveChannels() : void
    {
        $gauge = $this->registry->registerGauge(
            $this->prefix, 
            'active_channels', 
            'Current number of channels with clients subscribed', 
            ['type']
        );
        $gauge->set(
            $this->metrics['active_channels'], 
            ['blue']
        );
        return;
    }

    /**
     * 
     */
    protected function registerActivePatternSubscriptions() : void
    {
        $gauge = $this->registry->registerGauge(
            $this->prefix, 
            'active_pattern_subscriptions', 
            'Current number of active pattern subscriptions', 
            ['type']
        );
        $gauge->set(
            $this->metrics['active_pattern_subscriptions'], 
            ['blue']
        );
        return;
    }

    /**
     * 
     */
    protected function registerMetrics () : void
    {
        $this->registerQueueLen();
        $this->registerActiveChannels();
        $this->registerActivePatternSubscriptions();
        return;
    }

    /**
     * Return all metrics on plain text
     * 
     * @return string The whole text with all Prometheus metrics
     */
    public function renderMetrics () : string
    {
        return $this->renderer->render(
            $this->registry->getMetricFamilySamples()
        );
    }

    





}