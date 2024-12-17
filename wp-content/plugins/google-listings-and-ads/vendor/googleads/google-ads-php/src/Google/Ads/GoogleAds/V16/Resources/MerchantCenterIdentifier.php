<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/ads/googleads/v16/resources/product_link.proto

namespace Google\Ads\GoogleAds\V16\Resources;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The identifier for Google Merchant Center account
 *
 * Generated from protobuf message <code>google.ads.googleads.v16.resources.MerchantCenterIdentifier</code>
 */
class MerchantCenterIdentifier extends \Google\Protobuf\Internal\Message
{
    /**
     * Immutable. The customer ID of the Google Merchant Center account.
     * This field is required and should not be empty when creating a new
     * Merchant Center link. It is unable to be modified after the creation of
     * the link.
     *
     * Generated from protobuf field <code>optional int64 merchant_center_id = 1 [(.google.api.field_behavior) = IMMUTABLE];</code>
     */
    protected $merchant_center_id = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int|string $merchant_center_id
     *           Immutable. The customer ID of the Google Merchant Center account.
     *           This field is required and should not be empty when creating a new
     *           Merchant Center link. It is unable to be modified after the creation of
     *           the link.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Ads\GoogleAds\V16\Resources\ProductLink::initOnce();
        parent::__construct($data);
    }

    /**
     * Immutable. The customer ID of the Google Merchant Center account.
     * This field is required and should not be empty when creating a new
     * Merchant Center link. It is unable to be modified after the creation of
     * the link.
     *
     * Generated from protobuf field <code>optional int64 merchant_center_id = 1 [(.google.api.field_behavior) = IMMUTABLE];</code>
     * @return int|string
     */
    public function getMerchantCenterId()
    {
        return isset($this->merchant_center_id) ? $this->merchant_center_id : 0;
    }

    public function hasMerchantCenterId()
    {
        return isset($this->merchant_center_id);
    }

    public function clearMerchantCenterId()
    {
        unset($this->merchant_center_id);
    }

    /**
     * Immutable. The customer ID of the Google Merchant Center account.
     * This field is required and should not be empty when creating a new
     * Merchant Center link. It is unable to be modified after the creation of
     * the link.
     *
     * Generated from protobuf field <code>optional int64 merchant_center_id = 1 [(.google.api.field_behavior) = IMMUTABLE];</code>
     * @param int|string $var
     * @return $this
     */
    public function setMerchantCenterId($var)
    {
        GPBUtil::checkInt64($var);
        $this->merchant_center_id = $var;

        return $this;
    }

}

