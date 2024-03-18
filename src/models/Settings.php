<?php
namespace heroic\craftcacheinvalidator\models;
use Craft;
use craft\base\Model;

/**
 * AWS CloudFront Cache Invalidator settings
 */
class Settings extends Model
{
    public $enablePlugin;
    public $accessKeyId = '';
    public $secretAccessKey = '';
    public $region = '';
    public $distributionId = '';

    public function enablePluginComplex()
    {
        $envValue = getenv('AWS_CACHE_INVALIDATOR_ENABLE_PLUGIN');
        if ($envValue !== false) {
            return filter_var($envValue, FILTER_VALIDATE_BOOLEAN);
        } else {
            return $this->enablePlugin;
        }
    }


    public function init():void {
        parent::init();
    }

    public function rules(): array{
        return [
            ['enablePlugin', 'boolean'] // Ensure 'enablePlugin' is treated as a boolean
        ];
    }
}

