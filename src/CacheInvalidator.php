<?php

namespace heroic\craftcacheinvalidator;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\elements\Entry;
use craft\events\ModelEvent;
use craft\helpers\ElementHelper;
use craft\services\Utilities;
use craft\events\RegisterCacheOptionsEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use heroic\craftcacheinvalidator\models\Settings;
use heroic\craftcacheinvalidator\services\InvalidationService;
use heroic\craftcacheinvalidator\services\UtilityExtension;
use yii\base\Event;

use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;


//include_once "jobs/BatchInvalidateQueueJob.php";

/**
 * AWS CloudFront Cache Invalidator plugin
 *
 * @method static CacheInvalidator getInstance()
 * @method Settings getSettings()
 * @author Heroic <plugins@heroic.art>
 * @copyright Heroic
 * @license https://craftcms.github.io/license/ Craft License
 */
class CacheInvalidator extends Plugin
{
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;
    //private const JOB_COUNT_KEY = 'batchInvalidateQueueJobCount';

    public static function config(): array
    {
        return [
            'components' => [
                // Define component configs here...
            ],
        ];
    }

    public function init(): void
    {
        parent::init();

        Craft::setAlias('@cacheinvalidator', __DIR__);

        $this->setComponents([
            'invalidationService' => InvalidationService::class,
        ]);

        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function() {
            // ping invalidator
            $this->invalidationService->ping();

            // Register site routes event
            Event::on(
                UrlManager::class,
                UrlManager::EVENT_REGISTER_SITE_URL_RULES,
                function(RegisterUrlRulesEvent $event) {
                    $event->rules['cache-invalidator/ping'] = 'cache-invalidator/default/ping';
                    $event->rules['cache-invalidator/invalidate'] = 'cache-invalidator/default/invalidate';
                }
            );
            $this->onSaveInit();
            $this->utilitiesInit();
        });
    }

    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate('cache-invalidator/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }

    //////////////////////
    private function utilitiesInit(): void
    {
        Event::on(
            Utilities::class,
            Utilities::EVENT_REGISTER_UTILITY_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = UtilityExtension::class;
            }
        );
    }

    private function onSaveInit(): void
    {

        //$this->resetBatchInvalidateQueueJobCount();

        // Entry save handler
        $this->registerAfterSaveHandler(Entry::class);

        // Category save handler
        $this->registerAfterSaveHandler(\craft\elements\Category::class);

        // GlobalSet save handler
        $this->registerAfterSaveHandler(\craft\elements\GlobalSet::class);
    }
    private function registerAfterSaveHandler($elementType) {

        Event::on(
            $elementType,
            $elementType::EVENT_AFTER_SAVE,
            function (ModelEvent $event) {
                $element = $event->sender;
                if (
                    ElementHelper::isDraft($element) ||
                    ElementHelper::rootElement($element)->isProvisionalDraft ||
                    ElementHelper::isRevision($element)
                ) {
                    return;
                }

                //$this->incrementBatchInvalidateQueueJobCount();
                //$jobCount = $this->getBatchInvalidateQueueJobCount();
                //Craft::info('Job Count: ' . $jobCount, __METHOD__);

                // INVALIDATOR
                // will return a string such as 'single', 'channel', or 'structure'
                $sectionType = $element instanceof Entry ? $element->getSection()->type : 'N/A';
                $doGlobalInvalidate = false;
                if ($element instanceof \craft\elements\Category) {
                    $doGlobalInvalidate = true;
                } else if ($element instanceof \craft\elements\GlobalSet) {
                    $doGlobalInvalidate = true;
                }
                if ($sectionType == 'N/A') {
                    $doGlobalInvalidate = true;
                }

                // In your event handler method
                $elementUrl = $element->getUrl();
                if ($elementUrl && $doGlobalInvalidate == false) {
                    $parsedUrl = parse_url($elementUrl);
                    $path = $parsedUrl['path'];
                    $this->invalidationService->addPath($path);
                } else {
                    $this->invalidationService->addPath('');
                }
            }
        );
    }



    /*
    public function getBatchInvalidateQueueJobCount(): int
    {
        $ret = Craft::$app->session->get(self::JOB_COUNT_KEY, 0);
        if ($ret) {
            return $ret;
        } else {
            return 0;
        }
    }

    public function incrementBatchInvalidateQueueJobCount(): void
    {
        $currentCount = $this->getBatchInvalidateQueueJobCount();
        Craft::$app->session->set(self::JOB_COUNT_KEY, $currentCount + 1);
    }

    public function resetBatchInvalidateQueueJobCount(): void
    {
        Craft::$app->session->remove(self::JOB_COUNT_KEY);
    }
    */
}
