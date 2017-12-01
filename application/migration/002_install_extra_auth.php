<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Install_extra_auth extends CI_Migration {

    private $tables;

    public function __construct() {
        parent::__construct();
        $this->load->dbforge();
        $this->load->config('adauth');
    }

    public function up() {
      
        // Table structure for table 'groups'
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'MEDIUMINT',
                'constraint' => '8',
                'unsigned' => TRUE,
                'auto_increment' => FALSE,
                'comment' => 'The user id in this field is the same as the id in the users table.'
            ),
            'domain' => array(
                'type' => 'VARCHAR',
                'constraint' => '50',
            ),
            'theme' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
                'default' => 'bootstrap.min.css',
            ),
            'navbar' => array(
                'type' => 'VARCHAR',
                'constraint' => '20',
                 'default' => 'default',
            ),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table($this->config->item('user_extra', 'tables'));
    }

    public function down() {
        
    }

}
