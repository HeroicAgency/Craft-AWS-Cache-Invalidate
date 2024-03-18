<?php
namespace heroic\craftcacheinvalidator\jobs;

use Aws\CloudFront\CloudFrontClient;
use craft\queue\BaseJob;
use heroic\craftcacheinvalidator\CacheInvalidator; // Include this line
use craft\helpers\App;

class BatchInvalidateQueueJob extends BaseJob
{
    // Public Properties
    public $paths;

    // Job execution method
    public function execute($queue): void{
        // Fetch the entry by its ID

        $this->createTheInvalidation($this->paths);
    }

    private function createTheInvalidation($paths) {
        // make unique
        $paths = array_unique($paths);
        $hasBlanks = false;
        foreach ($paths as $value) {
            if ($value === '') {
                $hasBlanks = true;
            }
        }
        if ($hasBlanks) {
            $paths = ['/*'];
        }

        // Access plugin settings
        $settings = CacheInvalidator::getInstance()->getSettings();
        $region = App::parseEnv($settings->region);
        $accessKeyId = App::parseEnv($settings->accessKeyId);
        $secretAccessKey = App::parseEnv($settings->secretAccessKey);
        $distributionId = App::parseEnv($settings->distributionId);

        \Craft::info('===========');
        if ($region == '' || !$region) {
            $region = 'us-east-1';
        }
        \Craft::info('Region: '.  $region);
        \Craft::info('AccessKeyId: '.  $accessKeyId);
        //\Craft::info('$secretAccessKey: '.  $secretAccessKey);
        \Craft::info('DistributionId: '.  $distributionId);
        $cloudFrontClient = new CloudFrontClient([
            'version' => 'latest',
            'region'  => $region, // if no region, use us-east-1
            'credentials' => [ // Optionally specify credentials
                'key'    => $accessKeyId,
                'secret' => $secretAccessKey
            ]
        ]);

        $callerReference = uniqid(); // Unique value for each invalidation, e.g., a timestamp or unique ID

        $quantity = count($paths); // The number of paths you're invalidating
        \Craft::info('Paths: '.  print_r($paths, true));
        try {
            $result = $cloudFrontClient->createInvalidation([
                'DistributionId' => $distributionId,
                'InvalidationBatch' => [
                    'CallerReference' => $callerReference,
                    'Paths' => [
                        'Items' => $paths,
                        'Quantity' => $quantity,
                    ],
                ]
            ]);
            $message = 'Invalidation request successfully sent!<br><br>';
            if (isset($result['Location'])) {
                $message .= ' The invalidation location is: ' . $result['Location'];
            }
            if (isset($result['Invalidation'])) {
                $message .= "<pre>";
                $message .= print_r($result['Invalidation'], true);
                $message .= "</pre>";
            }
            return $message;
        } catch (AwsException $e) {
            return 'Error: ' . $e->getAwsErrorMessage();
        }
    }

    // Optional method to provide a default description for the job in the queue manager
    protected function defaultDescription(): ?string {
        //return 'Invalidating Cache: ' . $this->entryTitle  . ":::". $this->jobCount;
        //return 'Invalidating: ' . $this->entryTitle;
        return 'Invalidating Cache';
    }
}
