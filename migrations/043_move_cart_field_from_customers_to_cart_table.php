<?php
/**
 * Create cartand fill with records from cart field on customers table
 */

class Migration_move_cart_field_from_customers_to_cart_table extends TI_Migration
{
    public function up()
    {
        $fields = [
            'cart_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'customer_id INT(11) DEFAULT NULL',
            'session_id INT(11) DEFAULT NULL',
            'menu_id INT(11) DEFAULT NULL',
//            'code VARCHAR(128) DEFAULT NULL',
//            'class_name VARCHAR(128) DEFAULT NULL',
//            'description TEXT DEFAULT NULL',

            'option TEXT DEFAULT NULL',
            'quantity INT(11) DEFAULT 0',

//            'status TINYINT(1) DEFAULT 0',
//            'is_default TINYINT(1) DEFAULT 0',
//            'priority INT(11) DEFAULT 0',
            'date_added DATETIME DEFAULT NULL',
            'date_updated DATETIME DEFAULT NULL',
//            'UNIQUE (code)',
        ];

        $this->dbforge->add_field($fields);
        $this->dbforge->create_table('payments');
        $this->db->query('ALTER TABLE '.$this->db->dbprefix('payments').' AUTO_INCREMENT 11');

        $query = $this->db->get_where('extensions', ['type' => 'payment']);
        foreach ($query->result() as $row) {
            $this->db->set('name', $row->title);
            $this->db->set('code', $row->name);
            $this->db->set('class_name', ucwords($row->name).'\Payments\\'.ucwords($row->name));
            $this->db->set('data', $row->data);
            $this->db->set('status', $row->status);
            $this->db->set('is_default', 0);
            $this->db->set('date_added', mdate('%Y-%m-%d %H:%i:%a', time()));
            $this->db->set('date_updated', mdate('%Y-%m-%d %H:%i:%a', time()));
            $this->db->insert('payments');
        }

        $fields = [
            'payment_log_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'order_id INT(11) DEFAULT NULL',
            'payment_name VARCHAR(128) DEFAULT NULL',
            'message VARCHAR(255) DEFAULT NULL',
            'request TEXT DEFAULT NULL',
            'response TEXT DEFAULT NULL',
            'status TINYINT(1) DEFAULT NULL',
            'date_added DATETIME DEFAULT NULL',
            'date_updated DATETIME DEFAULT NULL',
        ];

        $this->dbforge->add_field($fields);
        $this->dbforge->create_table('payment_logs');
        $this->db->query('ALTER TABLE '.$this->db->dbprefix('payments').' AUTO_INCREMENT 11');
    }

    public function down()
    {
        $this->dbforge->drop_table('payments');
        $this->dbforge->drop_table('payment_logs');
    }
}