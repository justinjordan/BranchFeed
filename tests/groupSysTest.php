<?php


require_once('../src/php/Connection.php');
require_once('../src/php/GroupSystem.php');


class GroupSystemTest extends PHPUnit_Framework_TestCase
{
    public $db;
    public $groupSys;
    
    public function setUp()
    {
        $this->db = new Connection();
        $this->groupSys = new GroupSystem($this->db);
    }
    
    public function testFind()
    {
        $group = $this->groupSys->FindGroup(1);
        $this->assertNotEmpty($group);
        $this->assertTrue($group > 0);
    }
    
    public function testMembership()
    {
        $group = 0;
        $user = 0;
        
        
        $this->groupSys->AddToGroup( $group, $user ); // Add member
        $this->assertTrue( $this->groupSys->is_member($group, $user) ); // Test membership
        
        $members = $this->groupSys->GetMembers($group);
        $this->assertNotEmpty( $members );
        
        $this->groupSys->RemoveFromGroup( $group, $user ); // Remove member
        $this->assertFalse( $this->groupSys->is_member($group, $user) ); // Test membership
    }
}

