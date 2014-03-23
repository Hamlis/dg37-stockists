<?php
/**
 *
 * @author  Nerijus Eimanavičius <nerijus@eimanavicius.lt>
 */

header("Content-Type: application/json; charset=utf-8");

error_reporting(E_ALL);
ini_set('display_errors', 1);

class Stockist implements JsonSerializable
{
    protected $title;

    protected $url;

    protected $address;

    protected $phone;

    public function __construct($title, $url = null)
    {
        $this->setTitle($title);
        if (null != $url) {
            $this->setUrl($url);
        }
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
     * @param mixed $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $name
     */
    public function setTitle($name)
    {
        $this->title = $name;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }
}

function getElementContent(SimpleXMLElement $entry)
{
    return trim(strip_tags($entry->asXML()), "\n\r\t ");
}

function getFirstLinkHref(SimpleXMLElement $entry)
{
    $href = $entry->xpath('a[1]/@href');
    if (!$href) {
        return null;
    }
    return trim((string)$href[0]->href);
}

$list = array();
$last = null;
$xml = simplexml_load_file('stockists-old.phtml');
foreach ($xml->xpath('/table/tbody/tr') as $k => $entry) {
    if ($k % 2 == 0) {
        $title = getElementContent($entry->td[0]);
        $url = null;
        if (isset($entry->td[3])) {
            $url = getFirstLinkHref($entry->td[3]);
        }
        $list[] = $last = new Stockist($title, $url);
    } elseif ($last instanceof Stockist) {
        $address = getElementContent($entry->td[0]) . ', ' . getElementContent($entry->td[1]);
        $phone = getElementContent($entry->td[2]);
        if (', ' != $address) {
            $last->setAddress(trim($address, ', '));
        }
        if ($phone) {
            $last->setPhone($phone);
        }
        unset($last);
    }
}

$ref = null;
if (strpos($_SERVER['HTTP_REFERER'], 'http://demolt.ashop.me') === 0) {
    $ref = 'http://demolt.ashop.me';
} elseif (strpos($_SERVER['HTTP_REFERER'], 'http://dg37.com.au') === 0) {
    $ref = 'http://dg37.com.au';
} elseif (strpos($_SERVER['HTTP_REFERER'], 'http://www.dg37.com.au') === 0) {
    $ref = 'http://www.dg37.com.au';
}

if (null !== $ref) {
    header("Access-Control-Allow-Origin: " . $ref);
}

echo json_encode(array(
    'Stockist' => $list,
    'ref' => $_SERVER['HTTP_REFERER'],
));
