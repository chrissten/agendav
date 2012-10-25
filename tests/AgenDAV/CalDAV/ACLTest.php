<?php
namespace AgenDAV\CalDAV;

class ACLTest extends \PHPUnit_Framework_TestCase
{
    private $options1 = array(
        'owner' => array('read', 'write'),
        'authenticated' => array('read'),
        'unauthenticated' => array('read'),
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

    public function testACEOwner()
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

    public function testACEAuthenticated()
    {
        // Values: array(occurrences, children)
        $expected = array(
            '/ace' => array(1, 2),
            '/ace/principal' => array(1, 1),
            '/ace/principal/authenticated' => array(1, 0),
            '/ace/grant' => array(1, 1),
            '/ace/grant/privilege' => array(1, 1),
            '/ace/grant/privilege[1]' => array(1, 1),
            '/ace/grant/privilege[1]/read' => array(1, 0),
        );
        $acl = new ACL($this->options1);
        $d = new \DOMDocument();

        $ace1 = $acl->generateACE($d, 'authenticated');
        $this->assertInstanceOf('\DOMElement', $ace1);
        $d->appendChild($ace1);
        $xml = $d->saveXML();

        $this->checkXML($expected, $xml);
    }

    public function testACEUnauthenticated()
    {
        // Values: array(occurrences, children)
        $expected = array(
            '/ace' => array(1, 2),
            '/ace/principal' => array(1, 1),
            '/ace/principal/unauthenticated' => array(1, 0),
            '/ace/grant' => array(1, 1),
            '/ace/grant/privilege' => array(1, 1),
            '/ace/grant/privilege[1]' => array(1, 1),
            '/ace/grant/privilege[1]/read' => array(1, 0),
        );
        $acl = new ACL($this->options1);
        $d = new \DOMDocument();

        $ace1 = $acl->generateACE($d, 'unauthenticated');
        $this->assertInstanceOf('\DOMElement', $ace1);
        $d->appendChild($ace1);
        $xml = $d->saveXML();

        $this->checkXML($expected, $xml);
    }


    public function testACEPrincipalGiven()
    {
        // Values: array(occurrences, children)
        $expected = array(
            '/ace' => array(1, 2),
            '/ace/principal' => array(1, 1),
            '/ace/principal/href' => array(1, 0),
            '/ace/grant' => array(1, 2),
            '/ace/grant/privilege' => array(2, null),
            '/ace/grant/privilege[1]' => array(1, 1),
            '/ace/grant/privilege[1]/read' => array(1, 0),
            '/ace/grant/privilege[2]' => array(1, 1),
            '/ace/grant/privilege[2]/write' => array(1, 0),
        );
        $acl = new ACL($this->options1);
        $d = new \DOMDocument();

        $ace1 = $acl->generateACE($d, 'principal', '/my/test/principal', array('read', 'write'));
        $this->assertInstanceOf('\DOMElement', $ace1);
        $d->appendChild($ace1);
        $xml = $d->saveXML();

        $this->checkXML($expected, $xml);
    }


    public function testACL()
    {
        // Values: array(occurrences, children)
        // We are using now a default namespace, so we have to prefix each entry
        $expected = array(
            '/DAV:acl' => array(1, 3),
            '/DAV:acl/DAV:ace' => array(3, null),
            '/DAV:acl/DAV:ace[1]/DAV:principal' => array(1, 1),
            '/DAV:acl/DAV:ace[1]/DAV:principal/DAV:property' => array(1, 1),
            '/DAV:acl/DAV:ace[1]/DAV:principal/DAV:property/DAV:owner' => array(1, 0),
            '/DAV:acl/DAV:ace[1]/DAV:grant' => array(1, 2),
            '/DAV:acl/DAV:ace[1]/DAV:grant/DAV:privilege' => array(2, null),
            '/DAV:acl/DAV:ace[1]/DAV:grant/DAV:privilege[1]' => array(1, 1),
            '/DAV:acl/DAV:ace[1]/DAV:grant/DAV:privilege[1]/DAV:read' => array(1, 0),
            '/DAV:acl/DAV:ace[1]/DAV:grant/DAV:privilege[2]' => array(1, 1),
            '/DAV:acl/DAV:ace[1]/DAV:grant/DAV:privilege[2]/DAV:write' => array(1, 0),

            '/DAV:acl/DAV:ace[2]/DAV:principal' => array(1, 1),
            '/DAV:acl/DAV:ace[2]/DAV:principal/DAV:authenticated' => array(1, 0),
            '/DAV:acl/DAV:ace[2]/DAV:grant' => array(1, 1),
            '/DAV:acl/DAV:ace[2]/DAV:grant/DAV:privilege' => array(1, 1),
            '/DAV:acl/DAV:ace[2]/DAV:grant/DAV:privilege[1]' => array(1, 1),
            '/DAV:acl/DAV:ace[2]/DAV:grant/DAV:privilege[1]/DAV:read' => array(1, 0),

            '/DAV:acl/DAV:ace[3]/DAV:principal' => array(1, 1),
            '/DAV:acl/DAV:ace[3]/DAV:principal/DAV:unauthenticated' => array(1, 0),
            '/DAV:acl/DAV:ace[3]/DAV:grant' => array(1, 1),
            '/DAV:acl/DAV:ace[3]/DAV:grant/DAV:privilege' => array(1, 1),
            '/DAV:acl/DAV:ace[3]/DAV:grant/DAV:privilege[1]' => array(1, 1),
            '/DAV:acl/DAV:ace[3]/DAV:grant/DAV:privilege[1]/DAV:read' => array(1, 0),
        );
        $acl = new ACL($this->options1);

        $xml = $acl->getXML();

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

        // Register some namespaces
        $parsed_xml->registerXPathNamespace('DAV', 'DAV:');
        $parsed_xml->registerXPathNamespace('C', 'urn:ietf:params:xml:ns:caldav');

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
