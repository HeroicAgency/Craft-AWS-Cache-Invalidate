<?php
namespace heroic\craftcacheinvalidator\services;
use craft\base\Utility;
class UtilityExtension extends Utility
{
    public static function displayName(): string
    {
        return 'Invalidate AWS Cache';
    }

    public static function id(): string
    {
        return 'invalidate-aws-cache';
    }

    public static function iconPath(): string
    {
        // Path to an icon to use in the CP navigation, adjust the path as needed
        return \Craft::getAlias('@cacheinvalidator/assets/images/cloud.svg');
    }

    public static function contentHtml(): string
    {
        $invalidateUrl = \Craft::$app->urlManager->createUrl(['cache-invalidator/default/invalidate']);
        $pingUrl = \Craft::$app->urlManager->createUrl(['cache-invalidator/default/ping']);
        // Render the template and pass the `invalidateUrl` variable to it
        return \Craft::$app->getView()->renderTemplate('cache-invalidator/_utility.twig', [
            'invalidateUrl' => $invalidateUrl,
            'pingUrl' => $pingUrl,
        ]);
    }
}