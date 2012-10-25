<?php
namespace AgenDAV\CalDAV;

class ACLTest extends \PHPUnit_Framework_TestCase
{
    private $options1 = array(
        'owner' => array('read', 'write'),
        'authenticated' => array('read'),
        'unauthenticated' => array('C:read-free-busy'),
        'share_read' => array('read'),
        'share_rw' => array('read', 'write'),
    );

    public function __construct()
    {
    }

    public function testOptions1()
    {
        $acl = new ACL($this->options1);
        $this->assertEquals($this->options1, $acl->getOptions());
        unset($acl);

        $acl = new ACL();
        $acl->setOptions($this->options1);
        $this->assertEquals($this->options1, $acl->getOptions());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidOptions()
    {
        $acl = new ACL();
        $acl->setOptions(array());
    }

    public function testACE1()
    {
        // Values: array(occurrences, children)
        $expected = array(
            '/ace' => array(1, 2),
            '/ace/principal' => array(1, 1),
            '/ace/principal/property' => array(1, 1),
            '/ace/principal/property/owner' => array(1, 0),
            '/ace/grant' => array(1, 2),
            '/ace/grant/privilege' => array(2, null),
            '/ace/grant/privilege[1]' => array(1, 1),
            '/ace/grant/privilege[2]' => array(1, 1),
            '/ace/grant/privilege[1]/read' => array(1, 0),
            '/ace/grant/privilege[2]/write' => array(1, 0),
        );
        $acl = new ACL($this->options1);
        $d = new \DOMDocument();

        $ace1 = $acl->generateACE($d, 'owner');
        $this->assertInstanceOf('\DOMElement', $ace1);
        $d->appendChild($ace1);
        $xml = $d->saveXML();

        $this->checkXML($expected, $xml);
    }

    /**
     * Checks an XML against provided expected array
     *
     * @param mixed $expected
     * @param mixed $xml_text
     * @access private
     * @return void
     */
    private function checkXML($expected, $xml_text)
    {
        $parsed_xml = simplexml_load_string($xml_text);

        foreach ($expected as $path => $values) {
            list($occurrences, $children) = $values;
            $xpath = $parsed_xml->xpath($path);
            $found = count($xpath);
            $this->assertEquals(
                $occurrences,
                $found,
                'Expected ' . $occurrences . ' occurrences for ' . $path . ', found ' . $found
            );

            if ($occurrences == 1) {
                $found_children = $xpath[0]->count();
                $this->assertEquals(
                    $children,
                    $found_children,
                    'Expected ' . $children . ' children for ' . $path . ', found ' . $found_children
                );
            }
        }
    }

}
