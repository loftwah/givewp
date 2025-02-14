<?php

namespace Give\Framework\Http\Response\Types;

use Give\Framework\Http\Response\Traits\ResponseTrait;
use Give\Framework\Support\Contracts\Arrayable;
use Give\Framework\Support\Contracts\Jsonable;
use InvalidArgumentException;
use JsonSerializable;
use Symfony\Component\HttpFoundation\JsonResponse as BaseJsonResponse;

/**
 * @unreleased
 */
class JsonResponse extends BaseJsonResponse
{
    use ResponseTrait;

    /**
     * Constructor.
     *
     * @unreleased
     *
     * @param  mixed  $data
     * @param  int  $status
     * @param  array  $headers
     * @param  int  $options
     * @return void
     */
    public function __construct($data = null, $status = 200, $headers = [], $options = 0)
    {
        $this->encodingOptions = $options;

        parent::__construct($data, $status, $headers);
    }

    /**
     * Sets the JSONP callback.
     *
     * @unreleased
     *
     * @param  string|null  $callback
     * @return $this
     */
    public function withCallback($callback = null)
    {
        return $this->setCallback($callback);
    }

    /**
     * Get the json_decoded data from the response.
     *
     * @unreleased
     *
     * @param  bool  $assoc
     * @param  int  $depth
     * @return mixed
     */
    public function getData($assoc = false, $depth = 512)
    {
        return json_decode($this->data, $assoc, $depth);
    }

    /**
     * Sets the data to be sent as JSON.
     *
     * @unreleased
     *
     * @param  mixed  $data
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public function setData($data = [])
    {
        $this->original = $data;

        if ($data instanceof Jsonable) {
            $this->data = $data->toJson($this->encodingOptions);
        } elseif ($data instanceof JsonSerializable) {
            $this->data = json_encode($data->jsonSerialize(), $this->encodingOptions);
        } elseif ($data instanceof Arrayable) {
            $this->data = json_encode($data->toArray(), $this->encodingOptions);
        } else {
            $this->data = json_encode($data, $this->encodingOptions);
        }

        if (!$this->hasValidJson(json_last_error())) {
            throw new InvalidArgumentException(json_last_error_msg());
        }

        return $this->update();
    }

    /**
     * Determine if an error occurred during JSON encoding.
     *
     * @unreleased
     *
     * @param  int  $jsonError
     * @return bool
     */
    protected function hasValidJson($jsonError)
    {
        if ($jsonError === JSON_ERROR_NONE) {
            return true;
        }

        return $this->hasEncodingOption(JSON_PARTIAL_OUTPUT_ON_ERROR) &&
            in_array($jsonError, [
                JSON_ERROR_RECURSION,
                JSON_ERROR_INF_OR_NAN,
                JSON_ERROR_UNSUPPORTED_TYPE,
            ], true);
    }

    /**
     * Sets options used while encoding data to JSON.
     *
     * @unreleased
     *
     * @param  int  $encodingOptions
     *
     * @return $this
     */
    public function setEncodingOptions($encodingOptions)
    {
        $this->encodingOptions = (int)$encodingOptions;

        return $this->setData($this->getData());
    }

    /**
     * Determine if a JSON encoding option is set.
     *
     * @unreleased
     *
     * @param  int  $option
     * @return bool
     */
    public function hasEncodingOption($option)
    {
        return (bool)($this->encodingOptions & $option);
    }
}