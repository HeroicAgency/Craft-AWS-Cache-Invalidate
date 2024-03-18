<?php

namespace heroic\craftcacheinvalidator\services;

use Craft;
use craft\base\Component;
use yii\caching\Cache;
use heroic\craftcacheinvalidator\jobs\BatchInvalidateQueueJob;

class InvalidationService extends Component
{
    private $cacheKey = 'cacheInvalidatorPaths';
    private $batchTime = 3; // just a small delay for nested operations

    public function addPath($path)
    {
        if ($path == '') {
            $path = '/*';
        }

        // Insert the path into the custom table with a current timestamp
        Craft::$app->db->createCommand()
            ->insert('{{%invalidator_paths}}', [
                'path' => $path,
                'timestamp' => time(),
            ])
            ->execute();

        // Check if it's time to batch invalidate
        $this->checkForBatchInvalidation();
    }
    public function ping() {
        $this->checkForBatchInvalidation();
    }

    private function checkForBatchInvalidation()
    {
        $timeThreshold = time() - $this->batchTime;
        $pathsToInvalidate = (new \craft\db\Query())
            ->select(['path'])
            ->from('{{%invalidator_paths}}')
            ->where(['<', 'timestamp', $timeThreshold])
            ->column();

        if (!empty($pathsToInvalidate)) {
            $this->batchInvalidate($pathsToInvalidate);

            // Delete invalidated paths
            Craft::$app->db->createCommand()
                ->delete('{{%invalidator_paths}}', ['<', 'timestamp', $timeThreshold])
                ->execute();

            // force run queue
            $queue = Craft::$app->getQueue();
            $queue->run();
        }
    }

    private function batchInvalidate($paths)
    {
        // Trigger your BatchInvalidateQueueJob here with the paths
        Craft::$app->queue->push(new BatchInvalidateQueueJob(['paths' => $paths]));
    }
}
