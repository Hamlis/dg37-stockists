<?php

namespace DG37\Entity;

use JsonSerializable;

class Stockist implements JsonSerializable
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $address1 = '';

    /**
     * @var string
     */
    protected $address2 = '';

    /**
     * @var string
     */
    protected $city = '';

    /**
     * @var string
     */
    protected $state = '';

    /**
     * @var string
     */
    protected $postcode = '';

    /**
     * @var string
     */
    protected $phone = '';

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $image;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    /**
     * @param $title
     */
    public function setName($title)
    {
        $this->name = $title;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        if ($url && 0 !== strpos($url, 'http://') && 0 !== strpos($url, 'https://')) {
            $url = 'http://' . $url;
        }
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $address
     */
    public function setAddress1($address)
    {
        $this->address1 = $address;
    }

    /**
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * @param string $address
     */
    public function setAddress2($address)
    {
        $this->address2 = $address;
    }

    /**
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param string $postcode
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;
    }

    /**
     * @return string
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

}
