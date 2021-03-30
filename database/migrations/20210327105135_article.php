<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Article extends Migrator
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
        $table = $this->table('article', array('engine' => 'InnoDB', 'comment' => '公众号文章表', 'collation' => 'utf8mb4_general_ci'));
        $table->addColumn('fakeid', 'string', ['limit' => 50, 'default' => '', 'comment' => '公众号唯一标识', 'null' => false])
            ->addColumn('title', 'string', ['limit' => 255, 'default' => '', 'comment' => '文章标题', 'null' => false])
            ->addColumn('aid', 'string', ['limit' => 20, 'default' => '', 'comment' => '', 'null' => false])
            ->addColumn('album_id', 'string', ['limit' => 20, 'default' => '', 'comment' => '', 'null' => false])
            ->addColumn('appmsg_album_infos', 'string', ['limit' => 255, 'default' => '', 'comment' => '', 'null' => false])
            ->addColumn('appmsgid', 'string', ['limit' => 20, 'default' => '', 'comment' => '', 'null' => false])
            ->addColumn('checking', 'string', ['limit' => 10, 'default' => '', 'comment' => '', 'null' => false])
            ->addColumn('copyright_type', 'string', ['limit' => 10, 'default' => '', 'comment' => '', 'null' => false])
            ->addColumn('cover', 'string', ['limit' => 255, 'default' => '', 'comment' => '', 'null' => false])
            ->addColumn('create_time', 'string', ['limit' => 20, 'default' => '', 'comment' => '', 'null' => false])
            ->addColumn('update_time', 'string', ['limit' => 20, 'default' => '', 'comment' => '', 'null' => false])
            ->addColumn('digest', 'string', ['limit' => 255, 'default' => '', 'comment' => '', 'null' => false])
            ->addColumn('has_red_packet_cover', 'string', ['limit' => 10, 'default' => '', 'comment' => '', 'null' => false])
            ->addColumn('is_pay_subscribe', 'string', ['limit' => 10, 'default' => '', 'comment' => '', 'null' => false])
            ->addColumn('item_show_type', 'string', ['limit' => 10, 'default' => '', 'comment' => '', 'null' => false])
            ->addColumn('itemidx', 'string', ['limit' => 10, 'default' => '', 'comment' => '', 'null' => false])
            ->addColumn('link', 'string', ['limit' => 255, 'default' => '', 'comment' => '', 'null' => false])
            ->addColumn('media_duration', 'string', ['limit' => 10, 'default' => '', 'comment' => '', 'null' => false])
            ->addColumn('mediaapi_publish_status', 'string', ['limit' => 10, 'default' => '', 'comment' => '', 'null' => false])
            ->addColumn('pay_album_info', 'string', ['limit' => 255, 'default' => 1, 'comment' => '', 'null' => false])
            ->addColumn('tagid', 'string', ['limit' => 255, 'default' => '', 'comment' => '', 'null' => false])
            ->addColumn('is_del', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'default' => 0, 'comment' => '是否已被删除，0-否，1-是', 'null' => false])
            ->create();
    }
}
