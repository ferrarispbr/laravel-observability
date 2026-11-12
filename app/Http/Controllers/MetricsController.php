<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;

class MetricsController extends Controller
{
    public function __invoke(CollectorRegistry $registry): Response
    {
        $renderer = new RenderTextFormat();
        return response($renderer->render($registry->getMetricFamilySamples()), 200,['Content-Type' => RenderTextFormat::MIME_TYPE], );
    }
}
