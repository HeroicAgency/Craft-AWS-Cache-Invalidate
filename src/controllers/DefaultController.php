<?php

namespace heroic\craftcacheinvalidator\controllers;

use Craft;
use craft\web\Controller;
use heroic\craftcacheinvalidator\services\InvalidationService;
use heroic\craftcacheinvalidator\CacheInvalidator;

class DefaultController extends Controller
{
    // Allow anonymous access to this action
    //protected $allowAnonymous = ['invalidate'];
    protected int|bool|array $allowAnonymous = true;
    public function actionPing() {
        $invalidationService = CacheInvalidator::getInstance()->invalidationService;
        $invalidationService->ping();
        return $this->asJson(['message' => 'ping']);
    }

    public function actionInvalidate()
    {
        // Retrieve the "path" from the request body
        $path = \Craft::$app->getRequest()->getBodyParam('path', '');

        $invalidationService = CacheInvalidator::getInstance()->invalidationService;
        $invalidationService->addPath($path);

        // Respond with JSON indicating success
        $ret = 'Success.  AWS Cache invalidation initiated.';
        if ($path) {
            $ret .= ' for path: ' . $path;
        }
        return $this->asJson(['message' => $ret]);
    }
}
