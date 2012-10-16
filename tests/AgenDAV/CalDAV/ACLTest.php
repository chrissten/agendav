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

}
