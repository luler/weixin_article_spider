<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Weixin extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('weixin', array('engine' => 'InnoDB', 'comment' => '微信公众号表', 'collation' => 'utf8mb4_general_ci'));
        $table->addColumn('alias', 'string', ['limit' => 50, 'default' => '', 'comment' => '公众号账号', 'null' => false])
            ->addColumn('fakeid', 'string', ['limit' => 50, 'default' => '', 'comment' => '唯一标识', 'null' => false])
            ->addColumn('nickname', 'string', ['limit' => 100, 'default' => '', 'comment' => '名称', 'null' => false])
            ->addColumn('round_head_img', 'string', ['limit' => 255, 'default' => '', 'comment' => '图标', 'null' => false])
            ->addColumn('service_type', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'default' => 1, 'comment' => '公众号类型，0-个人订阅号，1-企业号，2-服务号', 'null' => false])
            ->addColumn('signature', 'string', ['limit' => 255, 'default' => '', 'comment' => '简介', 'null' => false])
            ->addIndex(['fakeid'], ['unique' => true])
            ->create();
    }
}
