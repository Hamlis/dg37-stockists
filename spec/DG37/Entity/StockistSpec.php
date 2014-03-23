<?php

namespace spec\DG37\Entity;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class StockistSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('Zanui');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('DG37\Entity\Stockist');
    }

    function it_is_json_serializable()
    {
        $this->shouldBeAnInstanceOf('JsonSerializable');
    }

    function it_has_name()
    {
        $this->getName()->shouldReturn('Zanui');
    }

    function it_should_allow_to_change_name()
    {
        $this->setName('Romeo');
        $this->getName()->shouldReturn('Romeo');
    }

    function it_should_have_url()
    {
        $this->setUrl('http://example.com');
        $this->getUrl()->shouldReturn('http://example.com');
    }

    function it_should_prefix_url_with_http()
    {
        $this->setUrl('example.com.au');
        $this->getUrl()->shouldReturn('http://example.com.au');
    }

    function it_should_have_optional_address()
    {
        $this->getAddress1()->shouldReturn('');
        $this->getAddress2()->shouldReturn('');
        $this->setAddress1('Bever and Ley');
        $this->setAddress2('Beverley');
        $this->getAddress1()->shouldReturn('Bever and Ley');
        $this->getAddress2()->shouldReturn('Beverley');
    }

    function it_should_have_optional_phone()
    {
        $this->getPhone()->shouldReturn('');
        $this->setPhone('08 8347 2374');
        $this->getPhone()->shouldReturn('08 8347 2374');
    }

    function it_should_serialize_all_his_fields()
    {
        $this->setName('Romeo');
        $this->setCity('Sidney');
        $this->setState('NWS');
        $this->setPostcode('2600');
        $this->setUrl('http://example.com');
        $this->setAddress1('Bever and Ley');
        $this->setAddress2('Beverley');
        $this->setPhone('08 8347 2374');
        $this->setImage('');
        $this->jsonSerialize()->shouldReturn(
            array(
                'name' => 'Romeo',
                'address1' => 'Bever and Ley',
                'address2' => 'Beverley',
                'city' => 'Sidney',
                'state' => 'NWS',
                'postcode' => '2600',
                'phone' => '08 8347 2374',
                'url' => 'http://example.com',
                'image' => '',
            )
        );
    }
}
